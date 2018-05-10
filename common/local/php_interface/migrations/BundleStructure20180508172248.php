<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class BundleStructure20180508172248 extends SprintMigrationBase
{

    protected $description = 'Структура для комплектов';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME'       => 'Bundle',
            'TABLE_NAME' => 'adv_bundle',
            'LANG'       =>
                [
                    'ru' =>
                        [
                            'NAME' => 'Комплект',
                        ],
                ],
        ]);
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_COUNT_ITEMS', [
            'FIELD_NAME'        => 'UF_COUNT_ITEMS',
            'USER_TYPE_ID'      => 'enumeration',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DISPLAY'          => 'LIST',
                    'LIST_HEIGHT'      => 1,
                    'CAPTION_NO_VALUE' => '',
                    'SHOW_NO_VALUE'    => 'Y',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Количество элементов',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Количество элементов',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Количество элементов',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Количество элементов',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_ACTIVE', [
            'FIELD_NAME'        => 'UF_ACTIVE',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DEFAULT_VALUE'  => '1',
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
                    'ru' => 'Активность',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Активность',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Активность',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Активность',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_NAME', [
            'FIELD_NAME'        => 'UF_NAME',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'S',
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
                    'ru' => 'Название',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Название',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Название',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Название',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PRODUCTS', [
            'FIELD_NAME'        => 'UF_PRODUCTS',
            'USER_TYPE_ID'      => 'hlblock',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'Y',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DISPLAY'       => 'LIST',
                    'LIST_HEIGHT'   => 5,
                    'HLBLOCK_ID'    => 47,
                    'HLFIELD_ID'    => 478,
                    'DEFAULT_VALUE' => 0,
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Товары',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Товары',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Товары',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Товары',
                ],
        ]);

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME'       => 'BundleItems',
            'TABLE_NAME' => 'adv_bundle_items',
            'LANG'       =>
                [
                    'ru' =>
                        [
                            'NAME' => 'Комплект(Товары)',
                        ],
                ],
        ]);
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_ACTIVE', [
            'FIELD_NAME'        => 'UF_ACTIVE',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DEFAULT_VALUE'  => '1',
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
                    'ru' => 'Активность',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Активность',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Активность',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Активность',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_QUANTITY', [
            'FIELD_NAME'        => 'UF_QUANTITY',
            'USER_TYPE_ID'      => 'double',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'PRECISION'     => 0,
                    'SIZE'          => 20,
                    'MIN_VALUE'     => 0.0,
                    'MAX_VALUE'     => 0.0,
                    'DEFAULT_VALUE' => 1.0,
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Количество',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Количество',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Количество',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Количество',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PRODUCT', [
            'FIELD_NAME'        => 'UF_PRODUCT',
            'USER_TYPE_ID'      => 'iblock_element',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'I',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DISPLAY'       => 'LIST',
                    'LIST_HEIGHT'   => 5,
                    'IBLOCK_ID'     => 3,
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'Y',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => ' Товар',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => ' Товар',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => ' Товар',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => ' Товар',
                ],
        ]);
    }
}
