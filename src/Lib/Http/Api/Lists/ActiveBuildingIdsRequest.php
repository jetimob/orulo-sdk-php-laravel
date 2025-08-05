<?php

namespace Jetimob\Orulo\Lib\Http\Api\Lists;

use Jetimob\Orulo\Lib\Http\Api\GetWClientAuth;

class ActiveBuildingIdsRequest extends GetWClientAuth
{
    protected string $updated_after;

    protected string $results_per_page = '100';

    protected string $page = '1';

    protected array $bodySchema = ['updated_after','results_per_page', 'page'];

    public function urn(): string
    {
        return 'buildings/ids/active';
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): ActiveBuildingIdsRequest
    {
        $this->page = $page;
        return $this;
    }

    public  function getUpdatedAfter(): string
    {
        return $this->updated_after;
    }

    public  function setUpdatedAfter(string $updated_after): ActiveBuildingIdsRequest
    {
        $this->updated_after = $updated_after;
        return $this;
    }

    public  function getResultsPerPage(): int
    {
        return $this->results_per_page;
    }

    public  function setResultsPerPage(int $results_per_page): ActiveBuildingIdsRequest
    {
        $this->results_per_page = $results_per_page;
        return $this;
    }
}
