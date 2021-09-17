<?php

namespace Jetimob\Orulo\Lib\Http\Model;

use Jetimob\Orulo\Lib\Traits\Serializable;

class PublicationLink
{
    use Serializable;

    protected string $url;

    /** @var bool Publication link status */
    protected bool $active;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return PublicationLink
     */
    public function setUrl(string $url): PublicationLink
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return PublicationLink
     */
    public function setActive(bool $active): PublicationLink
    {
        $this->active = $active;
        return $this;
    }

    public static function new(string $url, bool $active): self
    {
        return (new static())->setUrl($url)->setActive($active);
    }
}
