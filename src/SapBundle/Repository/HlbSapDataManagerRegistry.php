<?php

namespace FourPaws\SapBundle\Repository;

use Bitrix\Highloadblock\DataManager;
use Doctrine\Common\Collections\Collection;

class HlbSapDataManagerRegistry
{
    /**
     * @var Collection
     */
    protected $collection;

    public function register(string $sapProperty, DataManager $dataManager)
    {
        $this->collection->set($sapProperty, $dataManager);
        return $this;
    }

    /**
     * @param string $sapProperty
     *
     * @return mixed
     */
    public function get(string $sapProperty)
    {
        return $this->collection->get($sapProperty);
    }
}
