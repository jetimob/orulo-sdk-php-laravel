<?php

namespace Jetimob\Orulo\Lib\Http\Model;

trait BuildingTrait
{
    /** @var string $id building id */
    protected string $id;

    /** @var string $name building name */
    protected string $name;

    /** @var Developer $developer 'incorporadora' */
    protected Developer $developer;

    protected Publisher $publisher;

    protected Address $address;

    /** @var float $min_price minimum value of a building's unit */
    protected float $min_price;

    protected float $price_per_private_square_meter;

    /** @var int $min_bathrooms minimum number of bathrooms of all available units */
    protected int $min_bathrooms;

    /** @var int $max_bathrooms maximum number of bathrooms of all available units */
    protected int $max_bathrooms;

    /** @var int $min_area lowest area value among all available units */
    protected int $min_area;

    /** @var int highest area value among all available units */
    protected int $max_area;

    /** @var int $min_bedrooms minimum number of bedrooms among all available units */
    protected int $min_bedrooms;

    /** @var int $max_bedrooms maximum number of bedrooms among all available units */
    protected int $max_bedrooms;

    /** @var int $min_suites minimum number of suites among all available units */
    protected int $min_suites;

    /** @var int $max_suites maximum number of suites among all available units */
    protected int $max_suites;

    /** @var int $min_parking minimum number of parking spots among all available units */
    protected int $min_parking;

    /** @var int $max_parking maximum number of parking spots among all available units */
    protected int $max_parking;

    /** @var string $description property description */
    protected string $description;

    /** @var string $finality Enum: Comercial | Misto | Residencial */
    protected string $finality;

    protected DefaultImage $default_image;

    /** @var string $updated_at <date-time> (DD/MM/AAAA HH:MM:SS) of the building's last update */
    protected string $updated_at;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id ?? null;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * @return Developer
     */
    public function getDeveloper(): ?Developer
    {
        return $this->developer ?? null;
    }

    public function getPublisher(): ?Publisher
    {
        return $this->publisher ?? null;
    }

    /**
     * @return Address
     */
    public function getAddress(): ?Address
    {
        return $this->address ?? null;
    }

    /**
     * @return float
     */
    public function getMinPrice(): ?float
    {
        return $this->min_price ?? null;
    }

    /**
     * @return float
     */
    public function getPricePerPrivateSquareMeter(): ?float
    {
        return $this->price_per_private_square_meter ?? null;
    }

    /**
     * @return int
     */
    public function getMinBathrooms(): ?int
    {
        return $this->min_bathrooms ?? null;
    }

    /**
     * @return int
     */
    public function getMaxBathrooms(): ?int
    {
        return $this->max_bathrooms ?? null;
    }

    /**
     * @return int
     */
    public function getMinArea(): ?int
    {
        return $this->min_area ?? null;
    }

    /**
     * @return int
     */
    public function getMaxArea(): ?int
    {
        return $this->max_area ?? null;
    }

    /**
     * @return int
     */
    public function getMinBedrooms(): ?int
    {
        return $this->min_bedrooms ?? null;
    }

    /**
     * @return int
     */
    public function getMaxBedrooms(): ?int
    {
        return $this->max_bedrooms ?? null;
    }

    /**
     * @return int
     */
    public function getMinSuites(): ?int
    {
        return $this->min_suites ?? null;
    }

    /**
     * @return int
     */
    public function getMaxSuites(): ?int
    {
        return $this->max_suites ?? null;
    }

    /**
     * @return int
     */
    public function getMinParking(): ?int
    {
        return $this->min_parking ?? null;
    }

    /**
     * @return int
     */
    public function getMaxParking(): ?int
    {
        return $this->max_parking ?? null;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    /**
     * @return string
     */
    public function getFinality(): ?string
    {
        return $this->finality ?? null;
    }

    /**
     * @return DefaultImage
     */
    public function getDefaultImage(): ?DefaultImage
    {
        return $this->default_image ?? null;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updated_at ?? null;
    }
}
