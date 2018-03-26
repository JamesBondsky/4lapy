<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Catalog\ProductTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Exception;
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
    const PROPERTY_PREFIX = 'PROPERTY_';
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return AddResult
     *
     * @throws AddException
     * @throws AddProductException
     * @throws LoaderException
     * @throws ArgumentException
     * @throws Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $cIBlockElement = new \CIBlockElement();
    
        foreach ($data['PROPERTY_VALUE'] as &$value) {
            if (is_array($value) && $value['file'] === true) {
                unset($value['file']);
            }
        }
        
        $id = $cIBlockElement->Add($data, false, false);
        
        if (!$id) {
            throw new AddException(sprintf('IBlock %s element product #%s add error: %s',
                                           $this->getIblockId(),
                                           $primary,
                                           $cIBlockElement->LAST_ERROR));
        }
        
        /**
         * @todo переписать к чертям
         */
        if (is_array($data['CATALOG']) && $data['CATALOG']) {
            Loader::includeModule('catalog');
            
            $price = $data['CATALOG']['PRICE'];
            unset($data['CATALOG']['PRICE'], $data['CATALOG']['TIMESTAMP_X']);
            
            foreach ($data['CATALOG'] as $k => $v) {
                if (strpos($k, '_ORIG') !== false) {
                    unset($data['CATALOG'][$k]);
                }
            }

            $data['CATALOG']['VAT_INCLUDED'] = 'Y';
            $data['CATALOG']['ID'] = $id;
            
            try {
                $result = ProductTable::add($data['CATALOG']);
                
                if (!$result->isSuccess()) {
                    throw new AddProductException(sprintf('IBlock %s element product #%s add error: %s',
                                                          $this->getIblockId(),
                                                          $primary,
                                                          $cIBlockElement->LAST_ERROR));
                }
            } catch (AddProductException $e) {
                $cIBlockElement::Delete($id);
    
                throw new AddException(sprintf('IBlock %s element product #%s add error: %s',
                                               $this->getIblockId(),
                                               $primary,
                                               $e->getMessage()));
            } catch (Exception $e) {
                throw new AddException(sprintf('IBlock %s element product #%s add error: %s',
                                               $this->getIblockId(),
                                               $primary,
                                               $e->getMessage()));
            }
            
            \CPrice::SetBasePrice($id, $price, 'RUB');
        }
        
        MapTable::addEntity($this->entity, $primary, $id);
        
        if ($data['SECTIONS']) {
            $this->setInternalKeys(['sections' => $data['SECTIONS']], $id, $this->entity . '_section');
        }
        
        return new AddResult(true, $id);
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return UpdateResult
     *
     * @throws UpdateException
     * @throws UpdateProductException
     * @throws LoaderException
     * @throws ArgumentException
     * @throws Exception
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        $cIBlockElement = new \CIBlockElement();
    
        $this->deleteFilesBeforeUpdate($primary, $data);
    
        foreach ($data['PROPERTY_VALUE'] as &$value) {
            if (is_array($value) && $value['file'] === true) {
                unset($value['file']);
            }
        }
        
        if (!$cIBlockElement->Update($primary, $data, false, false, false, false)) {
            throw new UpdateException(sprintf('IBlock %s element #%s update error: %s',
                                              $this->getIblockId(),
                                              $primary,
                                              $cIBlockElement->LAST_ERROR));
        }
        
        $this->setInternalKeys(['sections' => $data['SECTIONS']], $primary, $this->entity . '_section');
        
        /**
         * @todo переписать к чертям
         */
        if (is_array($data['CATALOG']) && $data['CATALOG']) {
            Loader::includeModule('catalog');
            
            $price = $data['CATALOG']['PRICE'];
            unset($data['CATALOG']['PRICE'], $data['CATALOG']['TIMESTAMP_X']);
            
            foreach ($data['CATALOG'] as $k => $v) {
                if (strpos($k, '_ORIG') !== false) {
                    unset($data['CATALOG'][$k]);
                }
            }
            
            $data['CATALOG']['ID'] = $primary;
            
            try {
                $result = ProductTable::update($primary, $data['CATALOG']);
                
                if (!$result->isSuccess()) {
                    throw new UpdateProductException(sprintf('IBlock %s element product #%s update error: %s',
                                                             $this->getIblockId(),
                                                             $primary,
                                                             $result->getErrorMessages()));
                }
            } catch (Exception $e) {
                throw new UpdateException(sprintf('IBlock %s element product #{$primary} update error: %s',
                                                  $this->getIblockId(),
                                                  $e->getMessage()));
            }
            
            \CPrice::SetBasePrice($primary, $price, 'RUB');
        }
        
        return new UpdateResult(true, $primary);
    }
    
    /**
     * Set section list from data
     *
     * @param array  $data
     * @param string $internal
     * @param string $entity
     *
     * @throws ArgumentException
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
     * @return UpdateResult
     *
     * @throws UpdateException
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        if (strpos($field, self::PROPERTY_PREFIX) === false) {
            return $this->updateField($field, $primary, $value);
        }
        
        return $this->updateProperty(substr($field, strlen(self::PROPERTY_PREFIX)), $primary, $value);
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return UpdateResult
     * @throws UpdateException
     */
    public function updateField(string $field, string $primary, $value) : UpdateResult
    {
        $cIblockElement = new \CIBlockElement();
        
        if ($cIblockElement->Update($primary, [$field => $value])) {
            return new UpdateResult(true, $primary);
        }
    
        throw new UpdateException(sprintf('Update field with primary %s error: %s',
                                          $primary,
                                          $cIblockElement->LAST_ERROR));
    }
    
    /**
     * @param string $property
     * @param string $primary
     * @param        $value
     *
     * @return UpdateResult
     */
    public function updateProperty(string $property, string $primary, $value) : UpdateResult
    {
        (new \CIBlockElement())->SetPropertyValues($primary, $this->getIblockId(), $value, $property);
        
        /**
         * А вот здесь хер что мы отследим, Битрикс ничего не возвращаем. Считаем, что у нас никаких проблем нет.
         */
        return new UpdateResult(true, $primary);
    }
    
    /**
     * @todo КОСТЫЛЬ
     *
     * @param array  $data
     * @param string $primary
     */
    protected function deleteFilesBeforeUpdate(string $primary, array &$data)
    {
        foreach ($data['PROPERTY_VALUES'] as $code => &$value) {
            if (is_array($value) && $value['file']) {
                \CIBlockElement::SetPropertyValuesEx($primary,
                                                     $this->getIblockId(),
                                                     [$code => ['VALUE' => ['del' => 'Y']]]);
                
                unset($value['file']);
            }
        }
    }
}
