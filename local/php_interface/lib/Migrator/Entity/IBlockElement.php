<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Provider\Exceptions\AddException;
use FourPaws\Migrator\Provider\Exceptions\UpdateException;

/**
 * Class IBlockElement
 *
 * @package FourPaws\Migrator\Entity
 */
abstract class IBlockElement extends IBlock
{
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     * @throws \FourPaws\Migrator\Provider\Exceptions\AddException
     */
    public function addItem(string $primary, array $data) : Result
    {
        $cIBlockElement = new \CIBlockElement();
        
        $id = $cIBlockElement->Add($data, false, false, false);
        
        if (!$id) {
            throw new AddException("IBlock {$this->getIblockId()} element #{$primary} add error: $cIBlockElement->LAST_ERROR");
        }

        MapTable::addEntity($this->entity, $primary, $id);

        $this->setInternalKeys(['sections' => $data['SECTIONS']], $id, $this->entity . '_section');

        return (new AddResult(true, $id));
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     * @throws \FourPaws\Migrator\Provider\Exceptions\UpdateException
     */
    public function updateItem(string $primary, array $data) : Result
    {
        $cIBlockElement = new \CIBlockElement();
        
        if (!$cIBlockElement->Update($primary, $data, false, false, false, false)) {
            throw new UpdateException("IBlock {$this->getIblockId()} element #{$primary} update error: $cIBlockElement->LAST_ERROR");
        } else {
            $this->setInternalKeys(['sections' => $data['SECTIONS']], $primary, $this->entity . '_section');
        }
        
        return (new UpdateResult(true, $primary));
    }
    
    /**
     * Set section list from data
     *
     * @param array  $data
     * @param string $internal
     * @param string $entity
     */
    public function setInternalKeys(array $data, string $internal, string $entity)
    {
        if ($data['sections']) {
            $sectionList = MapTable::getInternalIdListByExternalIdList($data['sections'], $entity);
            
            (new \CIBlockElement())->SetElementSection($internal, $sectionList);
        }
    }
}