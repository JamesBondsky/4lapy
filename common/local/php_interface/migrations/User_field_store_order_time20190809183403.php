<?php

namespace Sprint\Migration;


class User_field_store_order_time20190809183403 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет поле \"[Нерегулярное] Время до которого можно оформить заказ\" для складов.";

    public function up()
    {
        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        $field = [
            "ENTITY_ID" => "CAT_STORE",
            "FIELD_NAME" => "UF_STORE_ORDER_TIMEI",
            "USER_TYPE_ID" => "string",
            "XML_ID" => "",
            "SORT" => "1350",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "SIZE" => 20,
                "ROWS" => 1,
                "REGEXP" => "",
                "MIN_LENGTH" => 0,
                "MAX_LENGTH" => 0,
                "DEFAULT_VALUE" => "",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "[Нерегулярное] Время до которого можно оформить заказ"
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "[Нерегулярное] Время до которого можно оформить заказ"
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "[Нерегулярное] Время до которого можно оформить заказ"
            ],
            "ERROR_MESSAGE" => [
                "ru" => ""
            ],
            "HELP_MESSAGE" => [
                "ru" => ""
            ],
        ];

        if ($userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $field['ENTITY_ID'],
            $field['FIELD_NAME'],
            $field
        )) {
            $this->log()->info('Пользовательское свойство ' . $field['FIELD_NAME'] . ' создано');
        } else {
            $this->log()->error('Ошибка при создании пользовательского свойства ' . $field['FIELD_NAME']);

            return false;
        }

        return true;
    }

    public function down()
    {
        return true;
    }

}
