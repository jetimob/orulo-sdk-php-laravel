<?php

namespace Jetimob\Orulo\Lib\Http\Model;

use Jetimob\Orulo\Lib\Traits\Serializable;

class ActiveBuildingIdItem
{
    use Serializable;

    protected string $id;
    protected string $updated_at;

    public function getId(): ?string
    {
        return $this->id ?? null;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at ?? null;
    }
}
