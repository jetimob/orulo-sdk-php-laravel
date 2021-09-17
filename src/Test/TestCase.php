<?php

namespace Jetimob\Orulo\Test;

use Jetimob\Orulo\Facade\Orulo;
use Jetimob\Orulo\Lib\Http\Api\ErrorResponse;
use Jetimob\Orulo\Lib\Http\Auth\AuthType;
use Jetimob\Orulo\Lib\Http\Auth\TokenResponse;
use Jetimob\Orulo\Lib\Http\Request;
use Jetimob\Orulo\Lib\Http\Response;
use Jetimob\Orulo\OruloServiceProvider;
use Jetimob\Orulo\Util\Console;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [OruloServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return ['orulo' => Orulo::class];
    }

    protected function assertResponse(Response $response)
    {
        if ($response->failed()) {
            $this->debugFailedResponse($response);
        } else {
            Console::log($response);
        }

        $this->assertFalse($response->failed());
    }

    protected function debugFailedResponse(Response $response)
    {
        if (!($response instanceof ErrorResponse)) {
            return;
        }

        Console::log($response);
        Console::log(sprintf('%s: %s', $response->getError(), $response->getErrorDescription()));
    }

    protected function makeToken(): TokenResponse
    {
        return TokenResponse::build();
    }

    /**
     * @param Request|string $request
     * @return mixed
     */
    protected function makeBaseRequest($request)
    {
        return Orulo::/*useAuthorizationToken($this->makeToken())->*/request(
            $request, $this->getClientId(),
            $this->getSecret(),
        );
    }

    /**
     * @param Request|string $request
     * @return mixed
     */
    protected function makeAndAssert($request): Response
    {
        $response = $this->makeBaseRequest($request);
        $this->assertResponse($response);

        return $response;
    }

    protected function getClientId(): string
    {
        return '';
    }

    protected function getSecret(): string
    {
        return '';
    }
}
