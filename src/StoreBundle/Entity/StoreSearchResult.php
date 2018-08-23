<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Entity;


use FourPaws\StoreBundle\Collection\StoreCollection;

class StoreSearchResult
{
    public const TYPE_LOCAL = 'local';
    public const TYPE_SUBREGIONAL = 'subregional';
    public const TYPE_REGIONAL = 'regional';
    public const TYPE_ALL = 'all';

    /**
     * @var string
     */
    protected $locationName = '';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var StoreCollection
     */
    protected $stores;

    /**
     * @return string
     */
    public function getLocationName(): string
    {
        return $this->locationName;
    }

    /**
     * @param string $locationName
     * @return StoreSearchResult
     */
    public function setLocationName(string $locationName): StoreSearchResult
    {
        $this->locationName = $locationName;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return StoreSearchResult
     */
    public function setType(string $type): StoreSearchResult
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return StoreCollection
     */
    public function getStores(): StoreCollection
    {
        return $this->stores;
    }

    /**
     * @param StoreCollection $stores
     * @return StoreSearchResult
     */
    public function setStores(StoreCollection $stores): StoreSearchResult
    {
        $this->stores = $stores;

        return $this;
    }
}
