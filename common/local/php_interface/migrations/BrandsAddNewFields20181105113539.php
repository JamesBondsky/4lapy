<?php

namespace Sprint\Migration;


class BrandsAddNewFields20181105113539 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавление новых свойств для брендов (детальная страница редизайн)';

    private $properties = [
        10 => [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'HTML',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Баннер встраиваемый в каталог',
            'SORT' => '19',
            'CODE' => 'CATALOG_INNER_BANNER',
            'FILE_TYPE' => '',
            'IS_REQUIRED' => 'N',
            'HINT' => '',
            'WITH_DESCRIPTION' => '',
            'MULTIPLE_CNT' => '5'
        ],
        0 => [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'BlocksShowSwitcher',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Переключение отображаемых блоков',
            'SORT' => '20',
            'CODE' => 'BLOCKS_SHOW_SWITCHER'
        ],
        3 => [
            'PROPERTY_TYPE' => 'F',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Баннер для десктопа',
            'SORT' => '30',
            'CODE' => 'BANNER_IMAGES_DESKTOP',
            'FILE_TYPE' => 'jpg, gif, bmp, png, jpeg',
            'IS_REQUIRED' => 'N',
            'USER_TYPE' => '',
            'HINT' => '',
            'WITH_DESCRIPTION' => 'N',
            'MULTIPLE_CNT' => '5'
        ],
        2 => [
            'PROPERTY_TYPE' => 'F',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Баннер для планшета',
            'SORT' => '40',
            'CODE' => 'BANNER_IMAGES_NOTEBOOK',
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
            'NAME' => 'Баннер для мобильного',
            'SORT' => '50',
            'CODE' => 'BANNER_IMAGES_MOBILE',
            'FILE_TYPE' => 'jpg, gif, bmp, png, jpeg',
            'IS_REQUIRED' => 'N',
            'USER_TYPE' => '',
            'HINT' => '',
            'WITH_DESCRIPTION' => 'N',
            'MULTIPLE_CNT' => '5'
        ],
        11 => [
            'PROPERTY_TYPE' => 'S',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Ссылка баннера',
            'SORT' => '51',
            'CODE' => 'BANNER_LINK',
            'IS_REQUIRED' => 'N',
            'USER_TYPE' => '',
            'HINT' => '',
            'WITH_DESCRIPTION' => 'N',
            'MULTIPLE_CNT' => '5'
        ],
        4 => [
            'PROPERTY_TYPE' => 'F',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Видео mp4',
            'SORT' => '60',
            'CODE' => 'VIDEO_MP4',
            'FILE_TYPE' => 'mp4',
            'IS_REQUIRED' => 'N',
            'USER_TYPE' => '',
            'HINT' => 'MP4 для Safari, IE9, iPhone, iPad, Android, и Windows Phone 7',
            'WITH_DESCRIPTION' => 'Y',
            'MULTIPLE_CNT' => '5'
        ],
        5 => [
            'PROPERTY_TYPE' => 'F',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Видео webm',
            'SORT' => '70',
            'CODE' => 'VIDEO_WEBM',
            'FILE_TYPE' => 'webm',
            'IS_REQUIRED' => 'N',
            'USER_TYPE' => '',
            'HINT' => 'WebM для Firefox4, Opera, и Chrome',
            'WITH_DESCRIPTION' => 'Y',
            'MULTIPLE_CNT' => '5'
        ],
        6 => [
            'PROPERTY_TYPE' => 'F',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Видео ogg',
            'SORT' => '80',
            'CODE' => 'VIDEO_OGG',
            'FILE_TYPE' => 'ogv',
            'IS_REQUIRED' => 'N',
            'USER_TYPE' => '',
            'HINT' => 'Ogg для старых версий Firefox и Opera',
            'WITH_DESCRIPTION' => 'Y',
            'MULTIPLE_CNT' => '5'
        ],
        7 => [
            'PROPERTY_TYPE' => 'S',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Заголовок к видео',
            'SORT' => '90',
            'CODE' => 'VIDEO_TITLE',
            'FILE_TYPE' => '',
            'IS_REQUIRED' => 'N',
            'HINT' => '',
            'WITH_DESCRIPTION' => '',
            'MULTIPLE_CNT' => '5'
        ],
        8 => [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'HTML',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Описание к видео',
            'SORT' => '100',
            'CODE' => 'VIDEO_DESCRIPTION',
            'FILE_TYPE' => '',
            'IS_REQUIRED' => 'N',
            'HINT' => '',
            'WITH_DESCRIPTION' => '',
            'MULTIPLE_CNT' => '5'
        ],
        12 => [
            'PROPERTY_TYPE' => 'F',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Превью изображение для видео',
            'SORT' => '101',
            'CODE' => 'VIDEO_PREVIEW_PICTURE',
            'FILE_TYPE' => 'jpg, gif, bmp, png, jpeg',
            'IS_REQUIRED' => 'N',
            'USER_TYPE' => '',
            'HINT' => '',
            'WITH_DESCRIPTION' => 'N',
            'MULTIPLE_CNT' => '5'
        ],
        9 => [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'ProductCategoriesProperty',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Товарные категории',
            'SORT' => '110',
            'CODE' => 'PRODUCT_CATEGORIES'
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
