<?php

namespace Jetimob\Orulo\Lib\Http\Model;

use Jetimob\Orulo\Lib\Traits\Serializable;

class Publisher
{
    use Serializable;

    protected ?string $id;

    protected ?string $name;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
