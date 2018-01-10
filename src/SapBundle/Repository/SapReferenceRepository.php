<?php

namespace FourPaws\SapBundle\Repository;

use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\SapBundle\ReferenceDirectory\SapReferenceStorage;

class SapReferenceRepository
{
    /**
     * @var SapReferenceStorage
     */
    private $sapReferenceStorage;

    public function __construct(SapReferenceStorage $sapReferenceStorage)
    {
        $this->sapReferenceStorage = $sapReferenceStorage;
    }

    /**
     * @param string $propertyCode
     * @param string $xmlId
     *
     * @return null|\FourPaws\BitrixOrm\Model\HlbReferenceItem
     */
    public function findByXmlId(string $propertyCode, string $xmlId)
    {
        return $this->sapReferenceStorage->findByXmlId($propertyCode, $xmlId);
    }


    public function create(string $propertyCode, HlbReferenceItem $item)
    {
        if (!$item->getCode()) {
        }
    }
}
