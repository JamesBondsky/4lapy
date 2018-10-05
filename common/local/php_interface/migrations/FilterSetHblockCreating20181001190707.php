<?php

namespace Sprint\Migration;


class FilterSetHblockCreating20181001190707 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Создание hBlock ";

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME' => 'FilterSet',
            'TABLE_NAME' => 'b_filter_set',
            'LANG' =>
                [
                    'ru' =>
                        [
                            'NAME' => 'Посадочные страницы',
                        ],
                ],
        ]);
        $entityId  = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_NAME', [
            'FIELD_NAME' => 'UF_NAME',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'Название посадочной страницы',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => '',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => '',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_ACTIVE', [
            'FIELD_NAME' => 'UF_ACTIVE',
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'DEFAULT_VALUE' => '1',
                    'DISPLAY' => 'CHECKBOX',
                    'LABEL' =>
                        [
                            0 => '',
                            1 => '',
                        ],
                    'LABEL_CHECKBOX' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'Акивность',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => '',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => '',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_URL', [
            'FIELD_NAME' => 'UF_URL',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'URL посадочной страницы',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => '',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => '',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_TARGET_URL', [
            'FIELD_NAME' => 'UF_TARGET_URL',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'Целевой URL с фильтрами',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => '',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => '',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_H1', [
            'FIELD_NAME' => 'UF_H1',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'Заголовок h1',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => '',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => '',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_TITLE', [
            'FIELD_NAME' => 'UF_TITLE',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'Title страницы',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => '',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => '',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DESCRIPTION', [
            'FIELD_NAME' => 'UF_DESCRIPTION',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'Description страницы',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => '',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => '',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_SEO_TEXT', [
            'FIELD_NAME' => 'UF_SEO_TEXT',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' =>
                [
                    'SIZE' => 30,
                    'ROWS' => 4,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL' =>
                [
                    'ru' => 'SEO-текст страницы',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => '',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => '',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => '',
                ],
        ]);
    }

    public function down()
    {

    }

}
