<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

class HLBlock_referrals20180112144121 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Реферальная программа';
    
    public function up()
    {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists(
            [
                'NAME'       => 'Referral',
                'TABLE_NAME' => 'adv_referral',
                'LANG'       => [
                    'ru' => [
                        'NAME' => 'Реферальная программа',
                    ],
                ],
            ]
        );
        $entityId  = 'HLBLOCK_' . $hlblockId;
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_CARD',
            [
                'FIELD_NAME'        => 'UF_CARD',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'Y',
                'SHOW_FILTER'       => 'S',
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
                    'ru' => 'Номер бонусной карты',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Номер бонусной карты',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Номер бонусной карты',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Номер бонусной карты',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_USER_ID',
            [
                'FIELD_NAME'        => 'UF_USER_ID',
                'USER_TYPE_ID'      => 'integer',
                'XML_ID'            => '',
                'SORT'              => '100',
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
            'UF_CARD_CLOSED_DATE',
            [
                'FIELD_NAME'        => 'UF_CARD_CLOSED_DATE',
                'USER_TYPE_ID'      => 'date',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'DEFAULT_VALUE' => [
                        'TYPE'  => 'NONE',
                        'VALUE' => '',
                    ],
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Дата окончания действия карты',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Дата окончания действия карты',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Дата окончания действия карты',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Дата окончания действия карты',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_MODERATED',
            [
                'FIELD_NAME'        => 'UF_MODERATED',
                'USER_TYPE_ID'      => 'boolean',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'DEFAULT_VALUE'  => '1',
                    'DISPLAY'        => 'CHECKBOX',
                    'LABEL'          => [
                        0 => '',
                        1 => '',
                    ],
                    'LABEL_CHECKBOX' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'На модерации',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'На модерации',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'На модерации',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'На модерации',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_LAST_NAME',
            [
                'FIELD_NAME'        => 'UF_LAST_NAME',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '200',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'S',
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
                    'ru' => 'Фамилия',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Фамилия',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Фамилия',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Фамилия',
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
                'SORT'              => '300',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'S',
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
                    'ru' => 'Имя',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Имя',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Имя',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Имя',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_SECOND_NAME',
            [
                'FIELD_NAME'        => 'UF_SECOND_NAME',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '400',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'S',
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
                    'ru' => 'Отчество',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Отчество',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Отчество',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Отчество',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_PHONE',
            [
                'FIELD_NAME'        => 'UF_PHONE',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '500',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'S',
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
                    'ru' => 'Телефон',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Телефон',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Телефон',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Телефон',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_EMAIL',
            [
                'FIELD_NAME'        => 'UF_EMAIL',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '600',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'S',
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
                    'ru' => 'Эл. почта ',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Эл. почта ',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Эл. почта ',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Эл. почта ',
                ],
            ]
        );
    }
    
    public function down()
    {
        $helper = new HelperManager();
        
        //your code ...
    }
}
