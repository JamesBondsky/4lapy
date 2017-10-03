<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Loader;
use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\AddProductException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;
use FourPaws\Migrator\Entity\Exceptions\UpdateProductException;

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
     * @return \FourPaws\Migrator\Entity\AddResult
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddProductException
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $cIBlockElement = new \CIBlockElement();
        
        $id = $cIBlockElement->Add($data, false, false, false);
        
        if (!$id) {
            throw new AddException("IBlock {$this->getIblockId()} element #{$primary} add error: $cIBlockElement->LAST_ERROR");
        }
        
        /**
         * @todo переписать к чертям
         */
        if ($data['CATALOG']) {
            Loader::includeModule('catalog');
            
            $price = $data['CATALOG']['PRICE'];
            unset($data['CATALOG']['PRICE'], $data['CATALOG']['TIMESTAMP_X']);

            foreach ($data['CATALOG'] as $k => $v) {
                if (strpos($k, '_ORIG') !== false) {
                    unset($data['CATALOG'][$k]);
                }
            }

            $data['CATALOG']['ID'] = $id;

            try {
                $result = ProductTable::add($data['CATALOG']);
    
                if (!$result->isSuccess()) {
                    throw new AddProductException("IBlock {$this->getIblockId()} element product #{$primary} add error: $cIBlockElement->LAST_ERROR");
                }
            } catch (AddProductException $e) {
                $cIBlockElement::Delete($id);

                throw new AddException("IBlock {$this->getIblockId()} element product #{$primary} add error: {$e->getMessage()}");
            } catch (\Throwable $e) {
                throw new AddException("IBlock {$this->getIblockId()} element product #{$primary} add error: {$e->getMessage()}");
            }

            \CPrice::SetBasePrice($id, $price, 'RUB');
        }

        MapTable::addEntity($this->entity, $primary, $id);

        if ($data['SECTIONS']) {
            $this->setInternalKeys(['sections' => $data['SECTIONS']], $id, $this->entity . '_section');
        }
        
        return (new AddResult(true, $id));
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateProductException
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        $cIBlockElement = new \CIBlockElement();
        
        if (!$cIBlockElement->Update($primary, $data, false, false, false, false)) {
            throw new UpdateException("IBlock {$this->getIblockId()} element #{$primary} update error: $cIBlockElement->LAST_ERROR");
        } else {
            $this->setInternalKeys(['sections' => $data['SECTIONS']], $primary, $this->entity . '_section');
        }
        
        $result = ProductTable::add($data['CATALOG']);
        
        if (!$result->isSuccess()) {
            throw new UpdateProductException("IBlock {$this->getIblockId()} element product #{$primary} update error: $cIBlockElement->LAST_ERROR");
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
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        if (strpos($field, 'PROPERTY_') === false) {
            return $this->updateField($field, $primary, $value);
        } else {
            return $this->updateProperty(str_replace('PROPERTY_', '', $field), $primary, $value);
        }
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     */
    public function updateField(string $field, string $primary, $value) : UpdateResult
    {
        $cIblockElement = new \CIBlockElement();
        
        if ($cIblockElement->Update($primary, [$field => $value])) {
            return new UpdateResult(true, $primary);
        }
        
        throw new UpdateException("Update field with primary {$primary} error: {$cIblockElement->LAST_ERROR}");
    }
    
    /**
     * @param string $property
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     */
    public function updateProperty(string $property, string $primary, $value) : UpdateResult
    {
        (new \CIBlockElement())->SetPropertyValues($primary, $this->getIblockId(), [$property => $value]);
        
        /**
         * А вот здесь хер что мы отследим, Битрикс ничего не возвращаем. Считаем, что у нас никаких проблем нет.
         */
        return new UpdateResult(true, $primary);
    }
}