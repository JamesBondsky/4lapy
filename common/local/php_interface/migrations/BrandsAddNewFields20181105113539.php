<?php

namespace Sprint\Migration;


class BrandsAddNewFields20181105113539 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавление новых свойств для брендов (детальная страница редизайн)';

    private $properties = [
        0 => [
            'PROPERTY_TYPE' => 'F',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'Y',
            'NAME' => 'Изображения для слайдера',
            'SORT' => '20',
            'CODE' => 'SLIDER_IMAGES',
            'FILE_TYPE' => 'jpg, gif, bmp, png, jpeg',
            'IS_REQUIRED' => 'N',
            'USER_TYPE' => '',
            'HINT' => '',
            'WITH_DESCRIPTION' => 'N',
            'MULTIPLE_CNT' => '5'
        ],
        1 => [
            'PROPERTY_TYPE' => 'F',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Видео',
            'SORT' => '30',
            'CODE' => 'VIDEO',
            'FILE_TYPE' => 'mpg, avi, wmv, mpeg, mpe, flv, mkv, mp4',
            'IS_REQUIRED' => 'N',
            'USER_TYPE' => '',
            'HINT' => '',
            'WITH_DESCRIPTION' => 'Y',
            'MULTIPLE_CNT' => '5'
        ],
        2 => [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'HTML',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Описание к видео',
            'SORT' => '40',
            'CODE' => 'VIDEO_DESCRIPTION',
            'FILE_TYPE' => '',
            'IS_REQUIRED' => 'N',
            'HINT' => '',
            'WITH_DESCRIPTION' => '',
            'MULTIPLE_CNT' => '5'
        ],
        3 => [
            'PROPERTY_TYPE' => 'G',
            'USER_TYPE' => '',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'Y',
            'NAME' => 'Разделы товаров',
            'SORT' => '50',
            'CODE' => 'SECTIONS',
            'FILE_TYPE' => '',
            'LINK_IBLOCK_TYPE_ID' => 'catalog',
            'LINK_IBLOCK_ID' => '2',
            'IS_REQUIRED' => 'N',
            'HINT' => '',
            'WITH_DESCRIPTION' => '',
            'MULTIPLE_CNT' => '25'
        ],
        4 => [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'BlocksShowSwitcher',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Переключение отображаемых блоков',
            'SORT' => '60',
            'CODE' => 'BLOCKS_SHOW_SWITCHER'
        ]
    ];

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID' => 'catalog',
            'CODE' => 'brands'
        ]);

        foreach ($this->properties as $property) {
            $helper->Iblock()->addPropertyIfNotExists($iblockId, array_merge($property, [
                'ACTIVE' => 'Y',
                'DEFAULT_VALUE' => false,
                'ROW_COUNT' => '1',
                'COL_COUNT' => '30',
                'XML_ID' => '',
                'TMP_ID' => NULL,
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'VERSION' => '2',
                'USER_TYPE_SETTINGS' => NULL
            ]));
        }
    }

    public function down()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID' => 'catalog',
            'CODE' => 'brands'
        ]);
        foreach ($this->properties as $property) {
            $helper->Iblock()->deletePropertyIfExists($iblockId, $property['CODE']);
        }
    }

}
