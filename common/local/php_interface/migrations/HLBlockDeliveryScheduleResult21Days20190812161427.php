<?php

namespace Sprint\Migration;


class HLBlockDeliveryScheduleResult21Days20190812161427 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет новое временной отрезок для расчётов по графикам поставов";

    const HL_BLOCK_NAME = "	DeliveryScheduleTPZ";

    public function up(){
        $helper = new HelperManager();

        $entityId  = 'HLBLOCK_'.$helper->Hlblock()->getHlblockId(static::HL_BLOCK_NAME);

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DAYS_21',
            [
                "FIELD_NAME" => "UF_DAYS_21",
                "USER_TYPE_ID" => "integer",
                "XML_ID" => "",
                "SORT" => "100",
                "MULTIPLE" => "N",
                "MANDATORY" => "N",
                "SHOW_FILTER" => "S",
                "SHOW_IN_LIST" => "Y",
                "EDIT_IN_LIST" => "Y",
                "IS_SEARCHABLE" => "N",
                "SETTINGS" => [
                    "SIZE" => 20,
                    "MIN_VALUE" => 0,
                    "MAX_VALUE" => 0,
                    "DEFAULT_VALUE" => "",
                ],
                "EDIT_FORM_LABEL" => [
                    "ru" => "Кол-во дней (отгрузка до 21)"
                ],
                "LIST_COLUMN_LABEL" => [
                    "ru" => "Кол-во дней (отгрузка до 21)"
                ],
                "LIST_FILTER_LABEL" => [
                    "ru" => "Кол-во дней (отгрузка до 21)"
                ],
                "ERROR_MESSAGE" => [
                    "ru" => ""
                ],
                "HELP_MESSAGE" => [
                    "ru" => ""
                ]
            ]
        );
    }

    public function down(){
        return true;
    }

}
