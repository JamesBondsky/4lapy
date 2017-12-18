<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

class HlBlock_comments20171211222846 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Создание HL блока комментариев';
    
    public function up()
    {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists(
            [
                'NAME'       => 'Comments',
                'TABLE_NAME' => 'adv_comments',
                'LANG'       => [
                    'ru' => [
                        'NAME' => 'Комментарии',
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
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'I',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'N',
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
            'UF_TEXT',
            [
                'FIELD_NAME'        => 'UF_TEXT',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'Y',
                'SHOW_FILTER'       => 'S',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'N',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 60,
                    'ROWS'          => 5,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Текст',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Текст',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Текст',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Текст',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_MARK',
            [
                'FIELD_NAME'        => 'UF_MARK',
                'USER_TYPE_ID'      => 'integer',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'Y',
                'SHOW_FILTER'       => 'I',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'N',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'MIN_VALUE'     => 0,
                    'MAX_VALUE'     => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Оценка',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Оценка',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Оценка',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Оценка',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_ACTIVE',
            [
                'FIELD_NAME'        => 'UF_ACTIVE',
                'USER_TYPE_ID'      => 'boolean',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'I',
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
                    'ru' => 'Активность',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Активность',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Активность',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Активность',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_OBJECT_ID',
            [
                'FIELD_NAME'        => 'UF_OBJECT_ID',
                'USER_TYPE_ID'      => 'integer',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'I',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'N',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'MIN_VALUE'     => 0,
                    'MAX_VALUE'     => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'ID элемента',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'ID элемента',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'ID элемента',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'ID элемента',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_TYPE',
            [
                'FIELD_NAME'        => 'UF_TYPE',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'Y',
                'SHOW_FILTER'       => 'S',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'N',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => 'iblock',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Тип',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Тип',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Тип',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Тип',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_DATE',
            [
                'FIELD_NAME'        => 'UF_DATE',
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
                        'TYPE'  => 'NOW',
                        'VALUE' => '',
                    ],
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Дата',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Дата',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Дата',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Дата',
                ],
            ]
        );
    }
    
    public function down()
    {
    }
}
