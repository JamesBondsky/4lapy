<?php

namespace Sprint\Migration;

class ActionTypeHighloadblock20171225165251 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    const ENTITY_NAME = 'ShareType';
    
    protected $description = 'Создание справочника типов акций';
    
    public function up()
    {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists([
                                                                   'NAME'       => self::ENTITY_NAME,
                                                                   'TABLE_NAME' => 'b_hlbd_sharetype',
                                                                   'LANG'       => [
                                                                       'ru' => [
                                                                           'NAME' => 'Тип акции',
                                                                       ],
                                                                   ],
                                                               ]);
        $entityId  = 'HLBLOCK_' . $hlblockId;
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId,
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
                                                                        'REGEXP'        => null,
                                                                        'MIN_LENGTH'    => 0,
                                                                        'MAX_LENGTH'    => 0,
                                                                        'DEFAULT_VALUE' => null,
                                                                    ],
                                                                    'EDIT_FORM_LABEL'   => [
                                                                        'ru' => 'Название',
                                                                    ],
                                                                    'LIST_COLUMN_LABEL' => [
                                                                        'ru' => 'Название',
                                                                    ],
                                                                    'LIST_FILTER_LABEL' => [
                                                                        'ru' => 'Название',
                                                                    ],
                                                                    'ERROR_MESSAGE'     => [
                                                                        'ru' => null,
                                                                    ],
                                                                    'HELP_MESSAGE'      => [
                                                                        'ru' => null,
                                                                    ],
                                                                ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId,
                                                                'UF_SORT',
                                                                [
                                                                    'FIELD_NAME'        => 'UF_SORT',
                                                                    'USER_TYPE_ID'      => 'integer',
                                                                    'XML_ID'            => '',
                                                                    'SORT'              => '300',
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
                                                                        'ru' => 'Сортировка',
                                                                    ],
                                                                    'LIST_COLUMN_LABEL' => [
                                                                        'ru' => 'Сортировка',
                                                                    ],
                                                                    'LIST_FILTER_LABEL' => [
                                                                        'ru' => 'Сортировка',
                                                                    ],
                                                                    'ERROR_MESSAGE'     => [
                                                                        'ru' => null,
                                                                    ],
                                                                    'HELP_MESSAGE'      => [
                                                                        'ru' => null,
                                                                    ],
                                                                ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId,
                                                                'UF_XML_ID',
                                                                [
                                                                    'FIELD_NAME'        => 'UF_XML_ID',
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
                                                                        'REGEXP'        => null,
                                                                        'MIN_LENGTH'    => 0,
                                                                        'MAX_LENGTH'    => 0,
                                                                        'DEFAULT_VALUE' => null,
                                                                    ],
                                                                    'EDIT_FORM_LABEL'   => [
                                                                        'ru' => 'Внешний код',
                                                                    ],
                                                                    'LIST_COLUMN_LABEL' => [
                                                                        'ru' => 'Внешний код',
                                                                    ],
                                                                    'LIST_FILTER_LABEL' => [
                                                                        'ru' => 'Внешний код',
                                                                    ],
                                                                    'ERROR_MESSAGE'     => [
                                                                        'ru' => null,
                                                                    ],
                                                                    'HELP_MESSAGE'      => [
                                                                        'ru' => null,
                                                                    ],
                                                                ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId,
                                                                'UF_LINK',
                                                                [
                                                                    'FIELD_NAME'        => 'UF_LINK',
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
                                                                        'REGEXP'        => null,
                                                                        'MIN_LENGTH'    => 0,
                                                                        'MAX_LENGTH'    => 0,
                                                                        'DEFAULT_VALUE' => null,
                                                                    ],
                                                                    'EDIT_FORM_LABEL'   => [
                                                                        'ru' => 'Ссылка',
                                                                    ],
                                                                    'LIST_COLUMN_LABEL' => [
                                                                        'ru' => 'Ссылка',
                                                                    ],
                                                                    'LIST_FILTER_LABEL' => [
                                                                        'ru' => 'Ссылка',
                                                                    ],
                                                                    'ERROR_MESSAGE'     => [
                                                                        'ru' => null,
                                                                    ],
                                                                    'HELP_MESSAGE'      => [
                                                                        'ru' => null,
                                                                    ],
                                                                ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId,
                                                                'UF_DESCRIPTION',
                                                                [
                                                                    'FIELD_NAME'        => 'UF_DESCRIPTION',
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
                                                                        'REGEXP'        => null,
                                                                        'MIN_LENGTH'    => 0,
                                                                        'MAX_LENGTH'    => 0,
                                                                        'DEFAULT_VALUE' => null,
                                                                    ],
                                                                    'EDIT_FORM_LABEL'   => [
                                                                        'ru' => 'Описание',
                                                                    ],
                                                                    'LIST_COLUMN_LABEL' => [
                                                                        'ru' => 'Описание',
                                                                    ],
                                                                    'LIST_FILTER_LABEL' => [
                                                                        'ru' => 'Описание',
                                                                    ],
                                                                    'ERROR_MESSAGE'     => [
                                                                        'ru' => null,
                                                                    ],
                                                                    'HELP_MESSAGE'      => [
                                                                        'ru' => null,
                                                                    ],
                                                                ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId,
                                                                'UF_FULL_DESCRIPTION',
                                                                [
                                                                    'FIELD_NAME'        => 'UF_FULL_DESCRIPTION',
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
                                                                        'REGEXP'        => null,
                                                                        'MIN_LENGTH'    => 0,
                                                                        'MAX_LENGTH'    => 0,
                                                                        'DEFAULT_VALUE' => null,
                                                                    ],
                                                                    'EDIT_FORM_LABEL'   => [
                                                                        'ru' => 'Полное описание',
                                                                    ],
                                                                    'LIST_COLUMN_LABEL' => [
                                                                        'ru' => 'Полное описание',
                                                                    ],
                                                                    'LIST_FILTER_LABEL' => [
                                                                        'ru' => 'Полное описание',
                                                                    ],
                                                                    'ERROR_MESSAGE'     => [
                                                                        'ru' => null,
                                                                    ],
                                                                    'HELP_MESSAGE'      => [
                                                                        'ru' => null,
                                                                    ],
                                                                ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId,
                                                                'UF_DEF',
                                                                [
                                                                    'FIELD_NAME'        => 'UF_DEF',
                                                                    'USER_TYPE_ID'      => 'boolean',
                                                                    'XML_ID'            => '',
                                                                    'SORT'              => '800',
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
                                                                            0 => null,
                                                                            1 => null,
                                                                        ],
                                                                        'LABEL_CHECKBOX' => null,
                                                                    ],
                                                                    'EDIT_FORM_LABEL'   => [
                                                                        'ru' => 'По умолчанию',
                                                                    ],
                                                                    'LIST_COLUMN_LABEL' => [
                                                                        'ru' => 'По умолчанию',
                                                                    ],
                                                                    'LIST_FILTER_LABEL' => [
                                                                        'ru' => 'По умолчанию',
                                                                    ],
                                                                    'ERROR_MESSAGE'     => [
                                                                        'ru' => null,
                                                                    ],
                                                                    'HELP_MESSAGE'      => [
                                                                        'ru' => null,
                                                                    ],
                                                                ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId,
                                                                'UF_FILE',
                                                                [
                                                                    'FIELD_NAME'        => 'UF_FILE',
                                                                    'USER_TYPE_ID'      => 'file',
                                                                    'XML_ID'            => '',
                                                                    'SORT'              => '900',
                                                                    'MULTIPLE'          => 'N',
                                                                    'MANDATORY'         => 'N',
                                                                    'SHOW_FILTER'       => 'N',
                                                                    'SHOW_IN_LIST'      => 'Y',
                                                                    'EDIT_IN_LIST'      => 'Y',
                                                                    'IS_SEARCHABLE'     => 'N',
                                                                    'SETTINGS'          => [
                                                                        'SIZE'             => 20,
                                                                        'LIST_WIDTH'       => 0,
                                                                        'LIST_HEIGHT'      => 0,
                                                                        'MAX_SHOW_SIZE'    => 0,
                                                                        'MAX_ALLOWED_SIZE' => 0,
                                                                        'EXTENSIONS'       => [],
                                                                    ],
                                                                    'EDIT_FORM_LABEL'   => [
                                                                        'ru' => 'Изображение',
                                                                    ],
                                                                    'LIST_COLUMN_LABEL' => [
                                                                        'ru' => 'Изображение',
                                                                    ],
                                                                    'LIST_FILTER_LABEL' => [
                                                                        'ru' => 'Изображение',
                                                                    ],
                                                                    'ERROR_MESSAGE'     => [
                                                                        'ru' => null,
                                                                    ],
                                                                    'HELP_MESSAGE'      => [
                                                                        'ru' => null,
                                                                    ],
                                                                ]);
    }
    
    public function down()
    {
        $hlBlockHelper = (new HelperManager())->Hlblock();
        $hlBlockHelper->deleteHlblock($hlBlockHelper->getHlblockId(self::ENTITY_NAME));
    }
    
}
