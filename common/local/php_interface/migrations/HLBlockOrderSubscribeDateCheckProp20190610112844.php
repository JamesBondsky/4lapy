<?php

namespace Sprint\Migration;


class HLBlockOrderSubscribeDateCheckProp20190610112844 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет свойство \"Дата оформления следующего заказа\" в HL-блок \"Подписка на доставку\"";
    const HL_BLOCK_NAME = "OrderSubscribe";

    public function up(){
        $helper = new HelperManager();

        $field = [
            "FIELD_NAME" => "UF_DATE_CHECK",
            "USER_TYPE_ID" => "datetime",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "DEFAULT_VALUE" => [
                  "TYPE" => "NONE",
                  "VALUE" => "",
                ],
                "USE_SECOND" => "Y",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Дата оформления следующего заказа",
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Дата оформления следующего заказа"
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Дата оформления следующего заказа"
            ],
            "ERROR_MESSAGE" => [
                "ru" => ""
            ],
            "HELP_MESSAGE" => [
                "ru" => ""
            ],
        ];

        $entityId  = 'HLBLOCK_'.$helper->Hlblock()->getHlblockId(static::HL_BLOCK_NAME);

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            $field['FIELD_NAME'],
            $field
        );

        return true;
    }

    public function down(){
        $helper = new HelperManager();
        return true;
    }

}
