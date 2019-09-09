<?php

namespace Sprint\Migration;


use CUserFieldEnum;

class HLBlockPetsAddSizeFields20190909134944 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет поля о размере питомца в HL-блок \"Питомцы\"";

    public function up(){
        $helper = new HelperManager();

        $field = [
            "FIELD_NAME" => "UF_SIZE",
            "USER_TYPE_ID" => "enumeration",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "S",
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
                "ru" => "Размер"
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Размер"
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Размер"
            ],
            "ERROR_MESSAGE" => [
                "ru" => ""
            ],
            "HELP_MESSAGE" => [
                "ru" => ""
            ],
            'ENUMS' => [
                'n1' => [
                    'XML_ID' => 'Monday',
                    'VALUE' => 'Понедельник',
                ],
                'n2' => [
                    'XML_ID' => 'Tuesday',
                    'VALUE' => 'Вторник',
                ],
                'n3' => [
                    'XML_ID' => 'Wednesday',
                    'VALUE' => 'Среда',
                ],
                'n4' => [
                    'XML_ID' => 'Thursday',
                    'VALUE' => 'Четверг',
                ],
                'n5' => [
                    'XML_ID' => 'Friday',
                    'VALUE' => 'Пятница',
                ],
                'n6' => [
                    'XML_ID' => 'Saturday',
                    'VALUE' => 'Суббота',
                ],
                'n7' => [
                    'XML_ID' => 'Sunday',
                    'VALUE' => 'Воскресенье',
                ],
            ],
        ];

        $entityId  = 'HLBLOCK_'.$helper->Hlblock()->getHlblockId('Pet');

        $helper->UserTypeEntity()->addField($entityId, $field);

    }

    public function down(){
        $helper = new HelperManager();

    }

    /**
     * @param $entityId
     * @param $field
     * @return bool
     */
    protected function addField($entityId, $field): bool
    {
        if ($fieldId = $this->userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $field['FIELD_NAME'],
            $field
        )) {
            $this->log()->info(sprintf(
                'Добавлено поле %s в HL-блок %s',
                $field['FIELD_NAME'],
                $entityId
            ));
        } else {
            $this->log()->error(sprintf(
                'Ошибка при добавлении поля %s в HL-блок %s',
                $field['FIELD_NAME'],
                $entityId
            ));
            return false;
        }

        if (isset($field['ENUMS'])) {
            $enum = new CUserFieldEnum();
            if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                $this->log()->info(sprintf('Добавлены значения для поля %s', $field['FIELD_NAME']));
            } else {
                $this->log()->error(sprintf('Не удалось добавить значения для поля %s', $field['FIELD_NAME']));
            }
        }

        return true;
    }

}
