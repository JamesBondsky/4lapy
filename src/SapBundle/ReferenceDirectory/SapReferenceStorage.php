<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

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
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @return null|HlbReferenceItem
     */
    public function findByXmlId(string $propertyCode, string $xmlId)
    {
        return $this->findByCallable($propertyCode, function (HlbReferenceItem $hlbReferenceItem) use ($xmlId) {
            return $hlbReferenceItem->getXmlId() === $xmlId;
        })->current() ?: null;
    }

    /**
     * @param string $propertyCode
     * @param array $propertyValuesXmlIds
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getNotExistingXmlIdList(string $propertyCode, array $propertyValuesXmlIds): array
    {
        $existingXmlIds = $this->referenceRepositoryRegistry->get($propertyCode)->getExistingXmlIds($propertyValuesXmlIds);
        $notExistingXmlIds = [];
        foreach ($propertyValuesXmlIds as $key => $xmlId) {
            if (!in_array($xmlId, $existingXmlIds, false)) {
                $notExistingXmlIds[] = $xmlId;
            }
        }

        return $notExistingXmlIds;
    }

    public function findByCode(string $propertyCode, string $code)
    {
        return $this->findByCallable($propertyCode, function (HlbReferenceItem $hlbReferenceItem) use ($code) {
            return $hlbReferenceItem->getCode() === $code;
        })->current() ?: null;
    }

    /**
     * @param string   $propertyCode
     * @param callable $callable
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @return Collection|HlbReferenceItem[]|HlbReferenceItemCollection
     */
    public function findByCallable(string $propertyCode, callable $callable)
    {
        if (!$this->collection->offsetExists($propertyCode)) {
            $this->log()->info(sprintf('Loading %s property', $propertyCode));
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
     * @throws \RuntimeException
     * @return static
     */
    public function clear(string $propertyCode)
    {
        $this->log()->info(sprintf('Clear %s property', $propertyCode));
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
