<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class Version20191126200745 extends SprintMigrationBase
{
    protected $description = 'Добавляет HL блок "Новый год: шансы пользователей"';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME' => 'NewYearUserChance',
            'TABLE_NAME' => '4lapy_new_year_user_chance',
            'LANG' => [
                'ru' => [
                    'NAME' => 'Новый год 2020: шансы пользователей',
                ],
            ],
        ]);
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_USER_ID', [
            'FIELD_NAME' => 'UF_USER_ID',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_USER_ID',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'MIN_VALUE' => 0,
                'MAX_VALUE' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => [
                'ru' => 'ID пользователя',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => '',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => '',
            ],
            'ERROR_MESSAGE' => [
                'ru' => '',
            ],
            'HELP_MESSAGE' => [
                'ru' => '',
            ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DATA', [
            'FIELD_NAME' => 'UF_DATA',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_DATA',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => [
                'ru' => 'Данные ',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => '',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => '',
            ],
            'ERROR_MESSAGE' => [
                'ru' => '',
            ],
            'HELP_MESSAGE' => [
                'ru' => '',
            ],
        ]);

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DATE_CREATE', [
            'FIELD_NAME' => 'UF_DATE_CREATE',
            'USER_TYPE_ID' => 'date',
            'XML_ID' => 'UF_DATE_CREATE',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'DEFAULT_VALUE' => [
                    'TYPE' => 'NONE',
                    'VALUE' => '',
                ],
            ],
            'EDIT_FORM_LABEL' => [
                'ru' => 'Дата регистрации',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => '',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => '',
            ],
            'ERROR_MESSAGE' => [
                'ru' => '',
            ],
            'HELP_MESSAGE' => [
                'ru' => '',
            ],
        ]);
    }

    public function down()
    {
        $helper = new HelperManager();
    }
}
