<?php

namespace FourPaws\BitrixOrm\Utils;

use Bitrix\Main;
use Bitrix\Main\Entity;
use CPerfomanceTable;

class EntityConstructor
{
    protected $className;
    
    protected $tableName;
    
    public function __construct($className, $tableName)
    {
        $this->className = $className;
        $this->tableName = $tableName;
    }
    
    public function compileEntity()
    {
        // build datamanager class
        $entity_name       = $this->className;
        $entity_data_class = $this->className;
        
        if (!preg_match('/^[a-z0-9_]+$/i', $entity_data_class)) {
            
            throw new Main\SystemException(
                sprintf(
                    'Invalid entity name `%s`.',
                    $entity_data_class
                )
            );
        }
        
        $entity_data_class .= 'Table';
        
        if (class_exists($entity_data_class)) {
            // rebuild if it's already exists
            Entity\Base::destroy($entity_data_class);
        }
        
        // make with an empty map
        $eval = '
				class ' . $entity_data_class . ' extends \Bitrix\Main\Entity\DataManager
				{
					public static function getTableName()
					{
						return ' . var_export($this->tableName, true) . ';
					}

					public static function getMap()
					{
						return ' . var_export($this->getFieldsMap(), true) . ';
					}
				}
			';
        
        eval($eval);
        
        // then configure and attach fields
        /** @var \Bitrix\Main\Entity\DataManager $entity_data_class */
        $entity = $entity_data_class::getEntity();
        
        /** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
        $uFields = $USER_FIELD_MANAGER->getUserFields('HLBLOCK_' . $hlblock['ID']);
        
        foreach ($uFields as $uField) {
            if ($uField['MULTIPLE'] == 'N') {
                // just add single field
                $params = [
                    'required' => $uField['MANDATORY'] == 'Y',
                ];
                $field  = $USER_FIELD_MANAGER->getEntityField($uField, $uField['FIELD_NAME'], $params);
                $entity->addField($field);
                foreach ($USER_FIELD_MANAGER->getEntityReferences($uField, $field) as $reference) {
                    $entity->addField($reference);
                }
            } else {
                // build utm entity
                static::compileUtmEntity($entity, $uField);
            }
        }
        
        return Entity\Base::getInstance($entity_name);
    }
    
    public function getFieldsMap($fields)
    {
    
    }
    
    public function getTableFields($tableName)
    {
        $obTable = new CPerfomanceTable;
        $obTable->Init($tableName);
        
        return $obTable->GetTableFields(false, true);
    }
}