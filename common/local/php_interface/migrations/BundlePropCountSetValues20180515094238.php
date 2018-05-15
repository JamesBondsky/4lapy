<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Helpers\HighloadHelper;

class BundlePropCountSetValues20180515094238 extends SprintMigrationBase {

    protected $description = 'установка значений свойства типа список "количество в наборе"';

    public function up(){
        $obUserField = new \CUserTypeEntity;

        $hlBundleId = HighloadHelper::getIdByName('Bundle');

        $fieldCountItemsId = $obUserField::GetList(
            [],
            [
                'FIELD_NAME' => 'UF_COUNT_ITEMS',
                'ENTITY_ID'  => 'HLBLOCK_' . $hlBundleId,
            ]
        )->Fetch()['ID'];

        $obEnum = new \CUserFieldEnum;
        $obEnum->SetEnumValues(
            $fieldCountItemsId,
            [
                'n0' => [
                    'VALUE'  => '2',
                    'XML_ID'  => '2',
                ],
                'n1' => [
                    'VALUE'  => '3',
                    'XML_ID'  => '3',
                ],
            ]
        );

    }
}
