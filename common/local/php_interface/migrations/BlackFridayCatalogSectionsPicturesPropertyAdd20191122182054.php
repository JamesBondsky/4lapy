<?php

namespace Sprint\Migration;


class BlackFridayCatalogSectionsPicturesPropertyAdd20191122182054 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = "Добавление свойства для картинок для разделов в инфоблок Черной Пятницы";
    
    public function up()
    {
        $helper = new HelperManager();
        $iblockId = \CIBlock::GetList([], ['CODE' => 'black_friday_sections'])->Fetch()['ID'];
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_DESKTOP_PICTURE', [
            'ENTITY_ID'         => 'IBLOCK_' . $iblockId . '_SECTION',
            'FIELD_NAME'        => 'UF_DESKTOP_PICTURE',
            'USER_TYPE_ID'      => 'file',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'SIZE'             => 20,
                    'LIST_WIDTH'       => 1000,
                    'LIST_HEIGHT'      => 1000,
                    'MAX_SHOW_SIZE'    => 0,
                    'MAX_ALLOWED_SIZE' => 0,
                    'EXTENSIONS'       =>
                        [
                        ],
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'en' => '',
                    'ru' => 'Картинка для десктопа',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Картинка для десктопа',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Картинка для десктопа',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'en' => null,
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'en' => null,
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_TABLET_PICTURE', [
            'ENTITY_ID'         => 'IBLOCK_' . $iblockId . '_SECTION',
            'FIELD_NAME'        => 'UF_TABLET_PICTURE',
            'USER_TYPE_ID'      => 'file',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'SIZE'             => 20,
                    'LIST_WIDTH'       => 1000,
                    'LIST_HEIGHT'      => 1000,
                    'MAX_SHOW_SIZE'    => 0,
                    'MAX_ALLOWED_SIZE' => 0,
                    'EXTENSIONS'       =>
                        [
                        ],
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'en' => '',
                    'ru' => 'Картинка для планшета',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Картинка для планшета',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Картинка для планшета',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'en' => null,
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'en' => null,
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_MOBILE_PICTURE', [
            'ENTITY_ID'         => 'IBLOCK_' . $iblockId . '_SECTION',
            'FIELD_NAME'        => 'UF_MOBILE_PICTURE',
            'USER_TYPE_ID'      => 'file',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'SIZE'             => 20,
                    'LIST_WIDTH'       => 1000,
                    'LIST_HEIGHT'      => 1000,
                    'MAX_SHOW_SIZE'    => 0,
                    'MAX_ALLOWED_SIZE' => 0,
                    'EXTENSIONS'       =>
                        [
                        ],
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'en' => '',
                    'ru' => 'Картинка для мобилки',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Картинка для мобилки',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Картинка для мобилки',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'en' => null,
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'en' => null,
                    'ru' => '',
                ],
        ]);
    }
    
    public function down()
    {
        $helper = new HelperManager();
        $iblockId = \CIBlock::GetList([], ['CODE' => 'black_friday_sections'])->Fetch()['ID'];
        
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_DESKTOP_PICTURE');
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_TABLET_PICTURE');
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_MOBILE_PICTURE');
    }
}
