<?php

namespace Jetimob\Orulo\Lib\Http\Api\Building;

use Jetimob\Orulo\Lib\Http\Api\PutRequest;
use Jetimob\Orulo\Lib\Http\Auth\AuthType;
use Jetimob\Orulo\Lib\Http\Model\PublicationLink;

class PublicationLinksRequest extends PutRequest
{
    protected string $buildingId;

    protected int $authType;

    protected array $bodySchema = ['publication_links'];

    /** @var PublicationLink[] */
    protected array $publication_links;

    /**
     * PublicationLinksRequest constructor.
     * @param string $buildingId
     * @param int $authType
     */
    public function __construct(string $buildingId, int $authType = AuthType::ORULO_CLIENT_AUTH)
    {
        parent::__construct();
        $this->buildingId = $buildingId;
        $this->authType = $authType;
    }

    public function urn(): string
    {
        return 'buildings/{buildingId}/publication_links';
    }

    public function authType(): ?int
    {
        return $this->authType;
    }

    /**
     * @return array
     */
    public function getPublicationLinks(): array
    {
        return $this->publication_links;
    }

    /**
     * @param array $publication_links
     * @return PublicationLinksRequest
     */
    public function setPublicationLinks(array $publication_links): PublicationLinksRequest
    {
        $this->publication_links = $publication_links;
        return $this;
    }
}
