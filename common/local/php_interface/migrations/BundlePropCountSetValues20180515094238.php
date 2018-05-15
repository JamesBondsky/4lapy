<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Helpers\HighloadHelper;

class BundlePropCountSetValues20180515094238 extends SprintMigrationBase {

    protected $description = 'установка значений свойства типа список "количество в наборе"';

    public function up(){
        $obUserField = new \CUserTypeEntity;

        $hlPetId = HighloadHelper::getIdByName('Bundle');

        $fieldGenderId = $obUserField::GetList(
            [],
            [
                'FIELD_NAME' => 'UF_COUNT_ITEMS',
                'ENTITY_ID'  => 'HLBLOCK_' . $hlPetId,
            ]
        )->Fetch()['ID'];

        $obEnum = new \CUserFieldEnum;
        $obEnum->SetEnumValues(
            $fieldGenderId,
            [
                'n0' => [
                    'VALUE'  => '2',
                ],
                'n1' => [
                    'VALUE'  => '2',
                ],
            ]
        );

    }
}
