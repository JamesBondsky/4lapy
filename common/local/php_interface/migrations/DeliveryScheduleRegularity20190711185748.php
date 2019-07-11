<?php

namespace Sprint\Migration;


use Bitrix\Sale\Internals\OrderPropsTable;
use CUserFieldEnum;
use FourPaws\Enum\HlblockCode;
use FourPaws\Enum\IblockCode;

class DeliveryScheduleRegularity20190711185748 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет поля \"Регулярность\" для расписаний поставок";

    public function up(){
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId(HlblockCode::DELIVERY_SCHEDULE_RESULT);
        $field = [
            "ENTITY_ID" => "HLBLOCK_".$hlblockId,
            "FIELD_NAME" => "UF_REGULARITY",
            "USER_TYPE_ID" => "enumeration",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "DISPLAY" => "LIST",
                "LIST_HEIGHT" => 5,
                "CAPTION_NO_VALUE" => "",
                "SHOW_NO_VALUE" => "Y",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Регулярность"
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Регулярность"
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Регулярность"
            ],
            "ERROR_MESSAGE" => [
                "ru" => ""
            ],
            "HELP_MESSAGE" => [
                "ru" => ""
            ],
            'ENUMS' => [
                'n1' => [
                    'XML_ID' => 'Z1',
                    'VALUE' => 'Регулярное',
                    'SORT' => '100',
                ],
                'n2' => [
                    'XML_ID' => 'Z2',
                    'VALUE' => 'Нерегулярное',
                    'SORT' => '200',
                ],
                'n3' => [
                    'XML_ID' => 'Z3',
                    'VALUE' => 'ТПЗ',
                    'SORT' => '300',
                ],
                'n4' => [
                    'XML_ID' => 'Z9',
                    'VALUE' => 'Исключения',
                    'SORT' => '500',
                ],
            ],
        ];

        $fieldId = $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $field['ENTITY_ID'],
            $field['FIELD_NAME'],
            $field
        );

        if (isset($field['ENUMS'])) {
            $enum = new CUserFieldEnum();
            if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                $this->log()->info(sprintf('Добавлены значения для поля %s', $field['FIELD_NAME']));
            } else {
                $this->log()->error(sprintf('Не удалось добавить значения для поля %s', $field['FIELD_NAME']));
            }
        }

        // ----
        $hlblockId = $helper->Hlblock()->getHlblockId(HlblockCode::DELIVERY_SCHEDULE);
        $field['ENTITY_ID'] = "HLBLOCK_".$hlblockId;

        $fieldId = $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $field['ENTITY_ID'],
            $field['FIELD_NAME'],
            $field
        );

        if (isset($field['ENUMS'])) {
            $enum = new CUserFieldEnum();
            if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                $this->log()->info(sprintf('Добавлены значения для поля %s', $field['FIELD_NAME']));
            } else {
                $this->log()->error(sprintf('Не удалось добавить значения для поля %s', $field['FIELD_NAME']));
            }
        }

        $prop = [
            "PERSON_TYPE_ID" => "1",
            "NAME" => "Тип расписания",
            "TYPE" => "STRING",
            "REQUIRED" => "N",
            "DEFAULT_VALUE" => "",
            "SORT" => "100",
            "USER_PROPS" => "N",
            "IS_LOCATION" => "N",
            "PROPS_GROUP_ID" => "3",
            "DESCRIPTION" => "",
            "IS_EMAIL" => "N",
            "IS_PROFILE_NAME" => "N",
            "IS_PAYER" => "N",
            "IS_LOCATION4TAX" => "N",
            "IS_FILTERED" => "N",
            "CODE" => "SCHEDULE_REGULARITY",
            "IS_ZIP" => "N",
            "IS_PHONE" => "N",
            "IS_ADDRESS" => "N",
            "ACTIVE" => "Y",
            "UTIL" => "N",
            "INPUT_FIELD_LOCATION" => "0",
            "MULTIPLE" => "N",
            "SETTINGS" => [
                "MINLENGTH" => "",
                "MAXLENGTH" => "",
                "PATTERN" => "",
                "MULTILINE" => "N",
                "SIZE" => "",
            ],
            "ENTITY_REGISTRY_TYPE" => "ORDER"
        ];

        $addResult = OrderPropsTable::add($prop);
        if (!$addResult->isSuccess()) {
            $this->log()->error('Ошибка при добавлении свойства заказа ' . self::PROP_CODE);

            return false;
        }

        return true;
    }

    public function down(){
        $helper = new HelperManager();
        return true;
    }

}
