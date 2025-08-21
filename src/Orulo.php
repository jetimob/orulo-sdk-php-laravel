<?php

namespace Jetimob\Orulo;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Jetimob\Http\Http;
use Jetimob\Http\Request as HttpRequest;
use Jetimob\Orulo\Exception\ConfigurationException;
use Jetimob\Orulo\Exception\EmptyResponseClassException;
use Jetimob\Orulo\Exception\OruloException;
use Jetimob\Orulo\Facade\Cache;
use Jetimob\Orulo\Lib\Http\Api\ErrorResponse;
use Jetimob\Orulo\Lib\Http\Auth\AuthType;
use Jetimob\Orulo\Lib\Http\Auth\TokenRequest;
use Jetimob\Orulo\Lib\Http\Auth\TokenResponse;
use Jetimob\Orulo\Lib\Http\Exception\AuthorizationException;
use Jetimob\Orulo\Lib\Http\Exception\MissingCodeException;
use Jetimob\Orulo\Lib\Http\Exception\RequiresEndUserInteraction;
use Jetimob\Orulo\Lib\Http\Exception\WrongAuthTypeException;
use Jetimob\Orulo\Lib\Http\Exception\WrongRequestTypeException;
use Jetimob\Orulo\Lib\Http\Exception\WrongResponseTypeException;
use Jetimob\Orulo\Lib\Http\Request;
use Jetimob\Orulo\Lib\Http\Response;
use Jetimob\Orulo\Util\Log;

class Orulo
{
    private const CONFIG_FILE_REQUIRED_KEYS = [
        'redirect_uri',
    ];

    private array $config;

    private ?TokenResponse $authorizationToken = null;

    private ?TokenResponse $testAuthorizationToken = null;

    private Http $client;

    /**
     * Orulo constructor.
     * @param array $config
     * @throws ConfigurationException
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        foreach (self::CONFIG_FILE_REQUIRED_KEYS as $key) {
            if (!array_key_exists($key, $config) || empty($config[$key])) {
                throw new ConfigurationException(
                    sprintf(
                        <<<MSG
Missing required keys in orulo's configuration file.
Please make sure to have all [%s] these keys in orulo.php file
MSG,
                        implode(', ', self::CONFIG_FILE_REQUIRED_KEYS),
                    ),
                );
            }
        }

        $this->client = new Http($config['http'] ?? []);
        Log::$enabled = $this->getConfig('logging', false);
    }

    /**
     * @param Request|string $request
     * @param string $clientId
     * @param string $clientSecret
     * @return Response
     * @throws AuthorizationException
     * @throws EmptyResponseClassException
     * @throws Lib\Http\Exception\MissingPropertyBodySchemaException
     * @throws MissingCodeException
     * @throws OruloException
     * @throws RequiresEndUserInteraction
     * @throws WrongAuthTypeException
     * @throws WrongRequestTypeException
     * @throws WrongResponseTypeException
     */
    public function request($request, string $clientId, string $clientSecret, bool $errored = false)
    {
        if (is_string($request)) {
            $request = new $request();
        }

        if (is_null($request) || !($request instanceof Request)) {
            throw new WrongRequestTypeException();
        }

        $oruloResponse = null;
        $builtBody = $request->build();
        $requestOptions = [];

        if (count($builtBody) > 0) {
            $requestOptions[$request->getBodyType()] = $builtBody;
        }

        if (!is_null($request->authType()) && !is_null($clientId)) {
            $requestOptions[RequestOptions::HEADERS] = [
                'Authorization' => "Bearer {$this->getAccessToken($clientId, $clientSecret, $request)}",
            ];
        }

        try {
            $response = $this->client->send(new HttpRequest(
                $request->getMethod(),
                $request->getUrn(),
                $requestOptions[RequestOptions::HEADERS] ?? [],
                $requestOptions[RequestOptions::BODY] ?? null
            ), $requestOptions);
            $responseClassName = $request->getResponseClass();

            if (empty($responseClassName)) {
                throw new EmptyResponseClassException();
            }

            if (!method_exists($responseClassName, 'deserialize')) {
                throw new WrongResponseTypeException(get_class($request));
            }

            /** @var Response $oruloResponse */
            $oruloResponse = $responseClassName::deserialize($response->getBody()->getContents());
            $oruloResponse->setStatusCode($response->getStatusCode());
        } catch (ClientException | ServerException $e) {
            $errorResponse = $e->getResponse();

            if (!$errored && $errorResponse->getStatusCode() === 401) {
                Cache::del($this->getCacheKey($clientId, $request->authType()));
                return $this->request($request, $clientId, $clientSecret, true);
            }

            $oruloResponse = ErrorResponse::deserialize($errorResponse->getBody()->getContents());
            $oruloResponse->setStatusCode($errorResponse->getStatusCode());
        } catch (Exception $e) {
            throw new OruloException($e->getMessage(), 0, $e);
        }

        $oruloResponse->initComplexObjects();
        return $oruloResponse;
    }

    /**
     * Returns an URL that the end user must be redirect to.
     *
     * This URL will ask for the user's permission (OAuth) and if he/she authorizes the usage, he/she will be redirected
     * to the 'redirect_uri' set in the configuration file. This URI must be registered through integracao@orulo.com.br.
     *
     * When the end user is redirected to the redirect_uri, there will be a query param named 'code' that will be used
     * to obtain an access_code. This can be made manually or through the 'handleAuthorizationResponse' method.
     *
     * @param string $clientId
     * @return string
     * @see http://api.orulo.com.br.s3-website-us-east-1.amazonaws.com/#section/Autenticacao-e-Autorizacao/oruloEndUserAuth
     * @noinspection PhpUnused
     */
    public function makeAuthorizationUrl(string $clientId): string
    {
        return sprintf(
            'https://www.orulo.com.br/oauth/authorize?client_id=%s&redirect_uri=%s&response_type=code',
            $clientId,
            $this->getConfig('redirect_uri'),
        );
    }

    /**
     * If there is a query param named 'code' in the request parameter, this function tries to exchange it to an access
     * token.
     *
     * If succeeded, the access token will be stored and attributed into the authorizationToken property.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $clientId
     * @param string $clientSecret
     * @return TokenResponse|ErrorResponse
     * @throws AuthorizationException
     * @throws EmptyResponseClassException
     * @throws Lib\Http\Exception\MissingPropertyBodySchemaException
     * @throws MissingCodeException
     * @throws OruloException
     * @throws RequiresEndUserInteraction
     * @throws WrongAuthTypeException
     * @throws WrongRequestTypeException
     * @throws WrongResponseTypeException
     * @noinspection PhpUnused
     */
    public function handleAuthorizationResponse(
        \Illuminate\Http\Request $request,
        string $clientId,
        string $clientSecret): Response
    {
        if (!$request->has('code')) {
            throw new MissingCodeException();
        }

        $response = $this->requestAndStoreToken(
            AuthType::ORULO_END_USER_AUTH,
            $clientId,
            $clientSecret,
            $request->get('code'),
        );

        if ($response->failed()) {
            return $response;
        }

        /** @var TokenResponse $response */
        $this->authorizationToken = $response;

        return $response;
    }

    /**
     * Returns the cache key used to retrieve a stored authorization response
     *
     * @param string $clientId
     * @param int $authType
     * @return string
     */
    public function getCacheKey(string $clientId, int $authType): string
    {
        return sprintf('ORULO:%d:%s', $authType, $clientId);
    }

    /**
     * Handles the lifecycle of an Orulo's access token.
     * The token is retrieved from the cache if there is one. If there is none, a new one will be requested to Orulo's
     * token generation endpoint.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param Request $request
     * @return Response
     * @throws AuthorizationException
     * @throws EmptyResponseClassException
     * @throws Lib\Http\Exception\MissingPropertyBodySchemaException
     * @throws MissingCodeException
     * @throws OruloException
     * @throws RequiresEndUserInteraction
     * @throws WrongAuthTypeException
     * @throws WrongRequestTypeException
     * @throws WrongResponseTypeException
     */
    private function retrieveAccessToken(string $clientId, string $clientSecret, Request $request): Response
    {
        $cached = $this->retrieveCached($clientId, $request->authType());

        if (!is_null($cached)) {
            if ($cached->getAuthType() !== $request->authType()) {
                throw new WrongAuthTypeException($request->authType(), $cached->getAuthType());
            }

            return $cached;
        }

        if ($request->authType() & AuthType::ORULO_END_USER_AUTH && !($request instanceof TokenRequest)) {
            throw new RequiresEndUserInteraction();
        }

        return $this->requestAndStoreToken($request->authType(), $clientId, $clientSecret);
    }

    /**
     * @param int $authType
     * @param string $clientId
     * @param string $clientSecret
     * @param string|null $code
     * @return Response
     * @throws AuthorizationException
     * @throws EmptyResponseClassException
     * @throws Lib\Http\Exception\MissingPropertyBodySchemaException
     * @throws OruloException
     * @throws RequiresEndUserInteraction
     * @throws WrongAuthTypeException
     * @throws WrongRequestTypeException
     * @throws WrongResponseTypeException
     * @throws MissingCodeException
     */
    private function requestAndStoreToken(
        int $authType,
        string $clientId,
        string $clientSecret,
        ?string $code = null
    ): Response
    {
        if ($authType & AuthType::ORULO_END_USER_AUTH && empty($code)) {
            throw new MissingCodeException();
        }

        /** @var TokenResponse $response */
        $response = $this->request(new TokenRequest($clientId, $clientSecret, $code), $clientId, $clientSecret);

        if ($response->failed()) {
            return $response;
        }

        $response->setAuthType($authType);
        Cache::set(
            $this->getCacheKey($clientId, $authType),
            $response->serialize(),
            'EX', // EX seconds -- Set the specified expire time, in seconds.
            now()->addSeconds($response->getExpiresIn())->unix(),
        );
        return $response;
    }

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param Request $request
     * @return string
     * @throws AuthorizationException
     * @throws EmptyResponseClassException
     * @throws Lib\Http\Exception\MissingPropertyBodySchemaException
     * @throws MissingCodeException
     * @throws OruloException
     * @throws RequiresEndUserInteraction
     * @throws WrongAuthTypeException
     * @throws WrongRequestTypeException
     * @throws WrongResponseTypeException
     */
    private function getAccessToken(string $clientId, string $clientSecret, Request $request): string
    {
        // remove local caching (making requests with different clientIds were using the same accessToken as it was
        // locally cached) and use only the one for testing.
        if (
            !is_null($this->testAuthorizationToken)
            && $this->testAuthorizationToken->getAuthType() === $request->authType()
        ) {
            return $this->testAuthorizationToken->getAccessToken();
        }

        /** @var TokenResponse $token */
        $token = $this->retrieveAccessToken($clientId, $clientSecret, $request);

        if ($token->failed()) {
            throw new AuthorizationException();
        }

        $this->authorizationToken = $token;

        if (!$this->isAccessTokenValid()) {
            Cache::del($this->getCacheKey($clientId, $token->getAuthType()));
            return $this->getAccessToken($clientId, $clientSecret, $request);
        }

        return $this->authorizationToken->getAccessToken();
    }

    /**
     * Returns true if the current access token is valid, false otherwise
     *
     * @return bool
     */
    private function isAccessTokenValid(): bool
    {
        if (is_null($this->authorizationToken)) {
            return false;
        }

        return $this->authorizationToken->isValid();
    }

    /**
     * Returns the configuration value from a given key. Default value is returned if the key is not found.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getConfig(string $key, $default = null)
    {
        return array_key_exists($key, $this->config) ? $this->config[$key] : $default;
    }

    /**
     * Use this function to override the access token to use in the request.
     * This function is only used in tests because the access token lifecycle is handled by the SDK.
     * I.e.: the credentials are automatically injected in the request when needed.
     *
     * @param TokenResponse $authorizationToken
     * @return $this
     * @noinspection PhpUnused
     */
    public function useAuthorizationToken(TokenResponse $authorizationToken): self
    {
        $this->testAuthorizationToken = $authorizationToken;
        return $this;
    }

    /**
     * Returns an instance of a TokenResponse stored in the cache.
     * Null if there is nothing cached.
     *
     * @param string $clientId
     * @param int $authType
     * @return TokenResponse|null
     */
    public function retrieveCached(string $clientId, int $authType): ?TokenResponse
    {
        $serialized = Cache::get($this->getCacheKey($clientId, $authType));

        if (empty($serialized)) {
            return null;
        }

        $cached = new TokenResponse();
        $cached->unserialize($serialized);

        return $cached;
    }
}
