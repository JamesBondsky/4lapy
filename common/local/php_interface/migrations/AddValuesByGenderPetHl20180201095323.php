<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use FourPaws\Helpers\HighloadHelper;

class AddValuesByGenderPetHl20180201095323 extends SprintMigrationBase
{
    
    protected $description = 'Добавление занчений для пола питомцев';
    
    /**
     * @return bool|void
     * @throws ArgumentException
     * @throws LoaderException
     */
    public function up()
    {
        $obUserField = new \CUserTypeEntity;
        
        $hlPetId = HighloadHelper::getIdByName('Pet');
        
        $fieldGenderId = $obUserField::GetList(
            [],
            [
                'FIELD_NAME' => 'UF_GENDER',
                'ENTITY_ID'  => 'HLBLOCK_' . $hlPetId,
            ]
        )->Fetch()['ID'];
        
        $obEnum = new \CUserFieldEnum;
        $obEnum->SetEnumValues(
            $fieldGenderId,
            [
                'n0' => [
                    'VALUE'  => 'Мальчик',
                    'XML_ID' => 'M',
                ],
                'n1' => [
                    'VALUE'  => 'Девочка',
                    'XML_ID' => 'F',
                ],
            ]
        );
    }
    
    public function down()
    {
    }
    
}
