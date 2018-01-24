<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use FourPaws\Helpers\HighloadHelper;

class ChangePetHl20180121002702 extends SprintMigrationBase
{
    
    protected $description = 'Добавление значение списка пола и изменение типа инфоблкоа в хайлоаде питомцев';
    
    public function up()
    {
        try {
            $obUserField = new \CUserTypeEntity;
            
            $hlPetId     = HighloadHelper::getIdByName('Pet');
            $fieldTypeId = $obUserField::GetList(
                [],
                [
                    'FIELD_NAME' => 'UF_TYPE',
                    'ENTITY_ID'  => 'HLBLOCK_' . $hlPetId,
                ]
            )->Fetch()['ID'];
            
            $hlForWhoId  = HighloadHelper::getIdByName('ForWho');
            $fieldNameId = $obUserField::GetList(
                [],
                [
                    'FIELD_NAME' => 'UF_TYPE',
                    'ENTITY_ID'  => 'HLBLOCK_' . $hlForWhoId,
                ]
            )->Fetch()['ID'];
            $obUserField->Update(
                $fieldTypeId,
                [
                    'SETTINGS' => [
                        'HLBLOCK_ID' => $hlForWhoId,
                        'HLFIELD_ID' => $fieldNameId,
                        'DISPLAY'    => 'LIST',
                    ],
                ]
            );
            
            $fieldGenderId = $obUserField::GetList(
                [],
                [
                    'FIELD_NAME' => 'UF_GENDER',
                    'ENTITY_ID'  => 'HLBLOCK_' . $hlForWhoId,
                ]
            )->Fetch()['ID'];
            
            $obEnum = new \CUserFieldEnum;
            $obEnum->SetEnumValues(
                $fieldGenderId,
                [
                    'n0' => [
                        'VALUE' => 'Мальчик',
                        'XML_ID' => 'M',
                    ],
                    'n1' => [
                        'VALUE' => 'Девочка',
                        'XML_ID' => 'F',
                    ],
                ]
            );
        } catch (ArgumentException $e) {
        } catch (LoaderException $e) {
        }
    }
    
    public function down()
    {
    }
    
}
