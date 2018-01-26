<?php

namespace Sprint\Migration;

class HLBlock_pets20180105013832 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = "set pets hl";
    
    public function up()
    {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists(
            [
                'NAME'       => 'Pet',
                'TABLE_NAME' => 'adv_pet',
                'LANG'       => [
                    'ru' => [
                        'NAME' => 'Питомцы',
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
                'MANDATORY'         => 'N',
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
            'UF_PHOTO',
            [
                'FIELD_NAME'        => 'UF_PHOTO',
                'USER_TYPE_ID'      => 'file',
                'XML_ID'            => '',
                'SORT'              => '100',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'N',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'SIZE'             => 20,
                    'LIST_WIDTH'       => 200,
                    'LIST_HEIGHT'      => 200,
                    'MAX_SHOW_SIZE'    => 0,
                    'MAX_ALLOWED_SIZE' => 0,
                    'EXTENSIONS'       => [],
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Фотография',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Фотография',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Фотография',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Фотография',
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
                    'ru' => 'Имя питомца',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Имя питомца',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Имя питомца',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Имя питомца',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_TYPE',
            [
                'FIELD_NAME'        => 'UF_TYPE',
                'USER_TYPE_ID'      => 'hlblock',
                'XML_ID'            => '',
                'SORT'              => '300',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'Y',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'DISPLAY'       => 'LIST',
                    'LIST_HEIGHT'   => 5,
                    'HLBLOCK_ID'    => 13,
                    'HLFIELD_ID'    => 102,
                    'DEFAULT_VALUE' => 0,
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Тип питомца',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Тип питомца',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Тип питомца',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Тип питомца',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_BREED',
            [
                'FIELD_NAME'        => 'UF_BREED',
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => '',
                'SORT'              => '400',
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
                    'ru' => 'Порода',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Порода',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Порода',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Порода',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_BIRTHDAY',
            [
                'FIELD_NAME'        => 'UF_BIRTHDAY',
                'USER_TYPE_ID'      => 'date',
                'XML_ID'            => '',
                'SORT'              => '500',
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
                    'ru' => 'Дата рождения',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Дата рождения',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Дата рождения',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Дата рождения',
                ],
            ]
        );
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            'UF_GENDER',
            [
                'FIELD_NAME'        => 'UF_GENDER',
                'USER_TYPE_ID'      => 'enumeration',
                'XML_ID'            => '',
                'SORT'              => '600',
                'MULTIPLE'          => 'N',
                'MANDATORY'         => 'Y',
                'SHOW_FILTER'       => 'N',
                'SHOW_IN_LIST'      => 'Y',
                'EDIT_IN_LIST'      => 'Y',
                'IS_SEARCHABLE'     => 'N',
                'SETTINGS'          => [
                    'DISPLAY'          => 'CHECKBOX',
                    'LIST_HEIGHT'      => 5,
                    'CAPTION_NO_VALUE' => '',
                ],
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Пол',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Пол',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Пол',
                ],
                'ERROR_MESSAGE'     => [
                    'ru' => '',
                ],
                'HELP_MESSAGE'      => [
                    'ru' => 'Пол',
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
