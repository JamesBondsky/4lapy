<?php

namespace Sprint\Migration;


class SmsForgotPasswordRestriction20181210171011 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Последняя отправка восстановления пароля";

    public function up()
    {
        $helper = new HelperManager();

        $entityId = 'USER';

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_SMS_FGT_PASS_TIME', [
            'FIELD_NAME' => 'UF_SMS_FORGOT_PASS_TIME',
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
                    'ru' => 'Последняя отправка восстановления пароля',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Последняя отправка восстановления пароля',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Последняя отправка восстановления пароля',
                ],
            'ERROR_MESSAGE' =>
                [
                    'ru' => 'Последняя отправка восстановления пароля',
                ],
            'HELP_MESSAGE' =>
                [
                    'ru' => 'Последняя отправка восстановления пароля',
                ],
        ]);
        return true;
    }

    public function down()
    {
        $helper = new HelperManager();

        $entityId = 'USER';
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists($entityId, 'UF_SMS_FGT_PASS_TIME');
        return true;
    }
}
