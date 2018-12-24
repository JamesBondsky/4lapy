<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class CreateModalCounter20181123143500 extends SprintMigrationBase
{

    protected $description = 'Добавление поля для подсчета показа модалок для ЛК + кол-во сессий.';

    public function up()
    {
        $helper = new HelperManager();

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('USER', 'UF_MODALS_CNTS', [
            'ENTITY_ID'         => 'USER',
            'FIELD_NAME'        => 'UF_MODALS_CNTS',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => '',
            'SORT'              => '999',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          => [
                'SIZE'          => 20,
                'ROWS'          => 1,
                'REGEXP'        => '',
                'MIN_LENGTH'    => 0,
                'MAX_LENGTH'    => 0,
                'DEFAULT_VALUE' => '0 0 0',
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Показы модалок',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Показы модалок',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Показы модалок',
            ],
            'ERROR_MESSAGE'     => [
                'ru' => '',
            ],
            'HELP_MESSAGE'      => [
                'ru' => 'Показы модалок',
            ],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('USER', 'UF_SESSION_CNTS', [
            'ENTITY_ID'         => 'USER',
            'FIELD_NAME'        => 'UF_SESSION_CNTS',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => '',
            'SORT'              => '999',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          => [
                'SIZE'          => 20,
                'MIN_VALUE'     => 0,
                'MAX_VALUE'     => 0,
                'DEFAULT_VALUE' => '0',
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Количество сессий',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Количество сессий',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Количество сессий',
            ],
            'ERROR_MESSAGE'     => [
                'ru' => '',
            ],
            'HELP_MESSAGE'      => [
                'ru' => 'Количество сессий',
            ],
        ]);
    }

    public function down(){
        $helper = new HelperManager();
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('USER', 'UF_MODALS_CNTS');
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('USER', 'UF_SESSION_CNTS');
    }
}
