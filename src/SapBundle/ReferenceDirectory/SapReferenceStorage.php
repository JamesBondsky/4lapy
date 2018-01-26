<?php

namespace FourPaws\SapBundle\ReferenceDirectory;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use Psr\Log\LoggerAwareInterface;

class SapReferenceStorage implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var Collection|HlbReferenceItemCollection[]
     */
    protected $collection;

    /**
     * @var ReferenceRepositoryRegistry
     */
    private $referenceRepositoryRegistry;

    public function __construct(ReferenceRepositoryRegistry $referenceRepositoryRegistry)
    {
        $this->collection = new ArrayCollection();
        $this->referenceRepositoryRegistry = $referenceRepositoryRegistry;
    }

    /**
     * @param string $propertyCode
     * @param string $xmlId
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
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
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @return Collection|HlbReferenceItem[]|HlbReferenceItemCollection
     */
    public function findByCallable(string $propertyCode, callable $callable)
    {
        if (!$this->collection->offsetExists($propertyCode)) {
            $this->log()->debug(sprintf('Loading %s property', $propertyCode));
            $this->collection->set($propertyCode, $this->referenceRepositoryRegistry->get($propertyCode)->findBy());
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
        $this->log()->debug(sprintf('Clear %s property', $propertyCode));
        $this->collection->remove($propertyCode);
        return $this;
    }

    /**
     * @return ReferenceRepositoryRegistry
     */
    public function getReferenceRepositoryRegistry(): ReferenceRepositoryRegistry
    {
        return $this->referenceRepositoryRegistry;
    }
}
