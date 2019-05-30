<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class UserFieldFromOldSite20190522092000 extends SprintMigrationBase
{

    protected $description = 'Добавление свойства пользователь со старого сайта';

    public function up()
    {
        $helper = new HelperManager();

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('USER', 'UF_FROM_OLD_SITE', [
            'ENTITY_ID'         => 'USER',
            'FIELD_NAME'        => 'UF_FROM_OLD_SITE',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => '',
            'SORT'              => '2000',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'Y',
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
                    'ru' => 'Пользователь со старого сайта',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Пользователь со старого сайта',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Пользователь со старого сайта',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Пользователь со старого сайта',
                ],
        ]);
    }

    public function down()
    {
        $helper = new HelperManager();
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('USER', 'UF_FROM_OLD_SITE');
    }
}
