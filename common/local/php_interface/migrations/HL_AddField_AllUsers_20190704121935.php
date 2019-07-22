<?php

namespace Sprint\Migration;


class HL_AddField_AllUsers_20190704121935 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавление галки "Отправить всем пользователям" в HL-блок "Push уведомления"';

    private $fieldName = 'UF_ALL_USERS';

    public function up()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME'       => 'PushMessages',
            'TABLE_NAME' => 'api_push_messages',
            'LANG'       =>
                [
                    'ru' =>
                        [
                            'NAME' => 'Push уведомления',
                        ],
                ],
        ]);
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, $this->fieldName, [
            'FIELD_NAME'        => $this->fieldName,
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'I',
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
                    'ru' => 'Отправить всем пользователям',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Отправить всем пользователям',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Отправить всем пользователям',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Если галка установлена, то поля "Отправлять всем пользователям, подписанным" и "Дополнительно отправлять указанным пользователям" игнорируются',
                ],
        ]);
    }

    public function down()
    {
        $helper = new HelperManager();
        $hlblockId = $helper->Hlblock()->getHlblockId('PushMessages');
        $entityId = 'HLBLOCK_' . $hlblockId;

        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, $this->fieldName);
    }

}
