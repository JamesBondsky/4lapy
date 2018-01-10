<?php

namespace FourPaws\SapBundle\ReferenceDirectory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;

class SapReferenceStorage
{
    /**
     * @var SapReferenceRegistry
     */
    protected $referenceRegistry;

    /**
     * @var Collection|HlbReferenceItemCollection[]
     */
    protected $collection;

    public function __construct(SapReferenceRegistry $referenceRegistry)
    {
        $this->referenceRegistry = $referenceRegistry;
        $this->collection = new ArrayCollection();
    }

    /**
     * @param string $propertyCode
     * @param string $xmlId
     *
     * @return null|HlbReferenceItem
     */
    public function findByXmlId(string $propertyCode, string $xmlId)
    {
        return $this->findByCallable($propertyCode, function (HlbReferenceItem $hlbReferenceItem) use ($xmlId) {
            return $hlbReferenceItem->getXmlId() === $xmlId;
        })->current();
    }

    public function findByCode(string $propertyCode, string $code)
    {
        return $this->findByCallable($propertyCode, function (HlbReferenceItem $hlbReferenceItem) use ($code) {
            return $hlbReferenceItem->getCode() === $code;
        })->current();
    }

    /**
     * @param string   $propertyCode
     * @param callable $callable
     *
     * @return Collection|HlbReferenceItem[]|HlbReferenceItemCollection
     */
    public function findByCallable(string $propertyCode, callable $callable)
    {
        if (!$this->collection->offsetExists($propertyCode)) {
            $this->collection->set($propertyCode, $this->loadCollection($propertyCode));
        }
        /**
         * @var Collection $collection
         */
        $collection = $this->collection->get($propertyCode);
        return $collection->filter($callable);
    }

    /**
     * @param string $propertyCode
     *
     * @return static
     */
    public function clear(string $propertyCode)
    {
        $this->collection->remove($propertyCode);
        return $this;
    }

    /**
     * @return SapReferenceRegistry
     */
    public function getReferenceRegistry(): SapReferenceRegistry
    {
        return $this->referenceRegistry;
    }

    protected function loadCollection(string $propertyCode)
    {
        return (new HlbReferenceQuery($this->referenceRegistry->get($propertyCode)::query()))->exec();
    }
}
