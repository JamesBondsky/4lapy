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
    
    
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_LABEL_LEFT', [
            'ENTITY_ID'         => 'IBLOCK_' . $iblockId . '_SECTION',
            'FIELD_NAME'        => 'UF_LABEL_LEFT',
            'USER_TYPE_ID'      => 'boolean',
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
                    'DEFAULT_VALUE'  => 0,
                    'DISPLAY'        => 'CHECKBOX',
                    'LABEL'          =>
                        [
                            0 => '',
                            1 => '',
                        ],
                    'LABEL_CHECKBOX' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Лэйбл слева',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Лэйбл слева',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Лэйбл слева',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => '',
                ],
        ]);
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_LABEL_RIGHT', [
            'ENTITY_ID'         => 'IBLOCK_' . $iblockId . '_SECTION',
            'FIELD_NAME'        => 'UF_LABEL_RIGHT',
            'USER_TYPE_ID'      => 'boolean',
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
                    'DEFAULT_VALUE'  => 0,
                    'DISPLAY'        => 'CHECKBOX',
                    'LABEL'          =>
                        [
                            0 => '',
                            1 => '',
                        ],
                    'LABEL_CHECKBOX' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Лэйбл справа',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Лэйбл справа',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Лэйбл справа',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => '',
                ],
        ]);
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_DISCOUNT_SIZE', [
            'ENTITY_ID'         => 'IBLOCK_' . $iblockId . '_SECTION',
            'FIELD_NAME'        => 'UF_DISCOUNT_SIZE',
            'USER_TYPE_ID'      => 'string',
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
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Размер скидки (для лэйбла)',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Размер скидки (для лэйбла)',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Размер скидки (для лэйбла)',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
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
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_LABEL_LEFT');
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_LABEL_RIGHT');
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('IBLOCK_' . $iblockId . '_SECTION', 'UF_DISCOUNT_SIZE');
    }
}
