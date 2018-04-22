<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class AddUserEsSubscribedBool20180422153208 extends SprintMigrationBase
{

    protected $description = 'Добавление флага подписки в ES';

    public function up()
    {
        $helper = new HelperManager();

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('USER', 'UF_ES_SUBSCRIBED', [
            'ENTITY_ID'         => 'USER',
            'FIELD_NAME'        => 'UF_ES_SUBSCRIBED',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => '',
            'SORT'              => '999',
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
                    'ru' => 'Подписан на рассылку',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Подписан на рассылку',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Подписан на рассылку',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Подписан на рассылку',
                ],
        ]);
    }
}
