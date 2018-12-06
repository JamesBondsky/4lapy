<?php

namespace Sprint\Migration;


class MobileAppBanner20181126122028 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Инфоблок для баннера мобильного приложения';

    private $properties = [
        0 => [
            'PROPERTY_TYPE' => 'S',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Ссылка на приложение в Google Play',
            'SORT' => '51',
            'CODE' => 'GOOGLE_PLAY_LINK',
            'IS_REQUIRED' => 'Y',
            'USER_TYPE' => '',
            'HINT' => '',
            'WITH_DESCRIPTION' => 'N',
            'MULTIPLE_CNT' => '5'
        ],
        1 => [
            'PROPERTY_TYPE' => 'S',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Ссылка на приложение в App Store',
            'SORT' => '51',
            'CODE' => 'APP_STORE_LINK',
            'IS_REQUIRED' => 'Y',
            'USER_TYPE' => '',
            'HINT' => '',
            'WITH_DESCRIPTION' => 'N',
            'MULTIPLE_CNT' => '5'
        ]
    ];

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblock([
            'NAME' => 'Баннер для мобильного приложения',
            'CODE' => 'mobile_app_banner',
            'IBLOCK_TYPE_ID' => 'publications',
            'SITE_ID' => ['s1'],
            'SORT' => 600,
            'GROUP_ID' => ['2' => 'R']
        ]);

        foreach ($this->properties as $property) {
            $helper->Iblock()->addPropertyIfNotExists($iblockId, array_merge($property, [
                'ACTIVE' => 'Y',
                'DEFAULT_VALUE' => false,
                'ROW_COUNT' => '1',
                'COL_COUNT' => '30',
                'XML_ID' => '',
                'TMP_ID' => null,
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'VERSION' => '2',
                'USER_TYPE_SETTINGS' => null
            ]));
        }

        $helper->Iblock()->addElement($iblockId,[
            'NAME' => 'Баннер для мобильного приложения',
            'XML_ID' => 'current_banner',
            'PROPERTY_GOOGLE_PLAY_LINK' => '',
            'PROPERTY_APP_STORE_LINK' => ''
        ]);

    }

    public function down()
    {
        $helper = new HelperManager();
        $helper->Iblock()->deleteIblockIfExists('mobile_app_banner');
    }

}
