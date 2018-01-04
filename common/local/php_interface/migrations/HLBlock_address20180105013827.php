<?php

namespace Sprint\Migration;

class HLBlock_address20180105013827 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = 'set address hl';
    
    public function up()
    {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists(
            [
                'NAME'       => 'Address',
                'TABLE_NAME' => 'adv_adress',
                'LANG'       => [
                    'ru' => [
                        'NAME' => 'Адреса',
                    ],
                ],
            ]
        );
        $entityId  = 'HLBLOCK_' . $hlblockId;
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_USER_ID',
            [
                'FIELD_NAME'        => 'UF_USER_ID',
                'USER_TYPE_ID'      => 'integer',
                'XML_ID'            => '',
                'SORT'              => '10',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'Y',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'MIN_VALUE'     => 0,
                    'MAX_VALUE'     => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Пользователь',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Пользователь',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Пользователь',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Пользователь',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_NAME',
            [
                'FIELD_NAME'        => 'UF_NAME',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Наименование',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Наименование',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Наименование',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Наименование',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_CITY_LOCATION',
            [
                'FIELD_NAME'        => 'UF_CITY_LOCATION',
                'USER_TYPE_ID'      => 'sale_location',
                'XML_ID'            => '',
                'SORT'              => '200',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Город(Местоположение)',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Город(Местоположение)',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Город(Местоположение)',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Город(Местоположение)',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_CITY',
            [
                'FIELD_NAME'        => 'UF_CITY',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '200',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'Y',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Город',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Город',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Город',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Город',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_STREET',
            [
                'FIELD_NAME'        => 'UF_STREET',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '300',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'Y',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Улица',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Улица',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Улица',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Улица',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_HOUSE',
            [
                'FIELD_NAME'        => 'UF_HOUSE',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '400',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'Y',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Дом',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Дом',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Дом',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Дом',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_HOUSING',
            [
                'FIELD_NAME'        => 'UF_HOUSING',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '500',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Корпус',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Корпус',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Корпус',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Корпус',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_ENTRANCE',
            [
                'FIELD_NAME'        => 'UF_ENTRANCE',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '600',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Подъезд',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Подъезд',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Подъезд',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Подъезд',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_FLOOR',
            [
                'FIELD_NAME'        => 'UF_FLOOR',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '700',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Этаж',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Этаж',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Этаж',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Этаж',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_FLAT',
            [
                'FIELD_NAME'        => 'UF_FLAT',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '800',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Квартира, офис',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Квартира, офис',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Квартира, офис',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Квартира, офис',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_INTERCOM_CODE',
            [
                'FIELD_NAME'        => 'UF_INTERCOM_CODE',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '900',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Код домофона',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Код домофона',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Код домофона',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Код домофона',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_MAIN',
            [
                'FIELD_NAME'        => 'UF_MAIN',
                'USER_TYPE_ID'      => 'boolean',
                'XML_ID'            => '',
                'SORT'              => '1000',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'DEFAULT_VALUE'  => 0,
                    'DISPLAY'        => 'CHECKBOX',
                    'LABEL'          => [
                        0 => '',
                        1 => '',
                    ],
                    'LABEL_CHECKBOX' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Основной',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Основной',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Основной',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Основной',
                ],
            ]
        );
    }
    
    public function down()
    {
    }
    
}
