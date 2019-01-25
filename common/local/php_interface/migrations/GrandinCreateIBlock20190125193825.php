<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class GrandinCreateIBlock20190125193825 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Создаёт инфоблок \"Заявки\" для лендинга Grandin";



    public function up(){
        $helper = new HelperManager();

        $helper->Iblock()->addIblockTypeIfNotExists([
            'ID' => IblockType::GRANDIN,
            'SORT' => 1000,
            'LANG' => Array(
                'ru' => Array(
                    'NAME' => 'Лендинг Grandin',
                    'SECTION_NAME' => 'Разделы',
                    'ELEMENT_NAME' => 'Элементы'
                ),
                'en' => Array(
                    'NAME' => 'Лендинг Grandin',
                    'SECTION_NAME' => 'Разделы',
                    'ELEMENT_NAME' => 'Элементы'
                ),
            )
        ]);

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'NAME' => 'Заявки',
            'CODE' => IblockCode::GRANDIN_REQUEST,
            'IBLOCK_TYPE_ID' => IblockType::GRANDIN,
            'SITE_ID' => ['s1', 's2'],
            'VERSION' => 2,
        ]);

        $properties = [
            [
                'NAME' => 'Пользователь',
                'SORT' => 50,
                'CODE' => 'USER',
                'PROPERTY_TYPE' => 'S:UserID',
            ],
            [
                'NAME' => 'Дата чека',
                'SORT' => 100,
                'CODE' => 'DATE',
                'PROPERTY_TYPE' => 'S:Date',
            ],
            [
                'NAME' => 'Сумма чека',
                'SORT' => 200,
                'CODE' => 'SUM',
                'PROPERTY_TYPE' => 'S',
            ],
            [
                'NAME' => 'Фамилия',
                'SORT' => 300,
                'CODE' => 'SURNAME',
                'PROPERTY_TYPE' => 'S',
            ],
            [
                'NAME' => 'Имя',
                'SORT' => 400,
                'CODE' => 'NAME',
                'PROPERTY_TYPE' => 'S',
            ],
            [
                'NAME' => 'Телефон',
                'SORT' => 500,
                'CODE' => 'PHONE',
                'PROPERTY_TYPE' => 'S',
            ],
            [
                'NAME' => 'Email',
                'SORT' => 600,
                'CODE' => 'EMAIL',
                'PROPERTY_TYPE' => 'S',
            ],
        ];

        foreach ($properties as $property) {
            $helper->Iblock()->addPropertyIfNotExists($iblockId, $property);
        }

    }

    public function down(){
        $helper = new HelperManager();

        $iblockTypeId = $helper->Iblock()->getIblockType(IblockType::GRANDIN);
        $helper->Iblock()->deleteIblockTypeIfExists($iblockTypeId);

        $iblockId = $helper->Iblock()->getIblockId(IblockCode::GRANDIN_REQUEST);
        $helper->Iblock()->deleteIblockIfExists($iblockId);

    }

}
