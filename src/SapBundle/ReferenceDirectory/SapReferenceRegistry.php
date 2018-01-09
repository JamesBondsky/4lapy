<?php

namespace FourPaws\SapBundle\ReferenceDirectory;

use Bitrix\Highloadblock\DataManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\SapBundle\Exception\NotFoundDataManagerException;

class SapReferenceRegistry
{
    /**
     * @var Collection
     */
    protected $collection;

    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }

    public function register(string $sapPropertyCode, DataManager $dataManager)
    {
        $this->collection->set($sapPropertyCode, $dataManager);
        return $this;
    }

    /**
     * @param string $sapPropertyCode
     *
     * @return DataManager
     * @throws NotFoundDataManagerException
     */
    public function get(string $sapPropertyCode)
    {
        $dataManager = $this->collection->get($sapPropertyCode);
        if ($dataManager) {
            return $dataManager;
        }

        throw new NotFoundDataManagerException(sprintf('DataManager for %s property not found', $sapPropertyCode));
    }
}
