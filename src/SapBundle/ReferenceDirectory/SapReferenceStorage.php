<?php

namespace FourPaws\SapBundle\ReferenceDirectory;

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
    }

    public function findByXmlId(string $propertyCode, string $xmlId)
    {
        return $this->findByCallable($propertyCode, function (HlbReferenceItem $hlbReferenceItem) use ($xmlId) {
            return $hlbReferenceItem->getXmlId() === $xmlId;
        })->current();
    }

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

    protected function loadCollection(string $propertyCode)
    {
        return (new HlbReferenceQuery($this->referenceRegistry->get($propertyCode)::query()))->exec();
    }
}
