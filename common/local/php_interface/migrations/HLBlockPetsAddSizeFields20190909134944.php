<?php

namespace Sprint\Migration;


use CUserFieldEnum;
use Sprint\Migration\Helpers\HlblockHelper;
use Sprint\Migration\Helpers\UserTypeEntityHelper;

class HLBlockPetsAddSizeFields20190909134944 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет поля о размере питомца в HL-блок \"Питомцы\"";

    /**
     * @var UserTypeEntityHelper
     */
    protected $userTypeEntityHelper;

    /**
     * @var HlblockHelper
     */
    protected $hlBlockHelper;

    public function __construct()
    {
        parent::__construct();
        $this->userTypeEntityHelper = $this->getHelper()->UserTypeEntity();
        $this->hlBlockHelper = $this->getHelper()->Hlblock();
    }

    public function up(){
        $helper = new HelperManager();

        $entityId  = 'HLBLOCK_'.$helper->Hlblock()->getHlblockId('Pet');

        $fields[] = [
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

        $fields[] = [
            "FIELD_NAME" => "UF_CHEST",
            "USER_TYPE_ID" => "double",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "S",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "PRECISION" => 4,
                "SIZE" => 20,
                "MIN_VALUE" => 0.0,
                "MAX_VALUE" => 0.0,
                "DEFAULT_VALUE" => "",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Обхват груди"
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Обхват груди"
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Обхват груди"
            ],
            "ERROR_MESSAGE" => [
                "ru" => ""
            ],
            "HELP_MESSAGE" => [
                "ru" => ""
            ]
        ];

        $fields[] = [
            "FIELD_NAME" => "UF_BACK",
            "USER_TYPE_ID" => "double",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "PRECISION" => 4,
                "SIZE" => 20,
                "MIN_VALUE" => 0.0,
                "MAX_VALUE" => 0.0,
                "DEFAULT_VALUE" => "",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Длина спины"
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Длина спины"
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Длина спины"
            ],
            "ERROR_MESSAGE" => [
                "ru" => ""
            ],
            "HELP_MESSAGE" => [
                "ru" => ""
            ]
        ];

        $fields[] = [
            "FIELD_NAME" => "UF_NECK",
            "USER_TYPE_ID" => "double",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "PRECISION" => 4,
                "SIZE" => 20,
                "MIN_VALUE" => 0.0,
                "MAX_VALUE" => 0.0,
                "DEFAULT_VALUE" => "",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Обхват шеи"
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Обхват шеи"
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Обхват шеи"
            ],
            "ERROR_MESSAGE" => [
                "ru" => ""
            ],
            "HELP_MESSAGE" => [
                "ru" => ""
            ]
        ];

        foreach ($fields as $field){
            $helper->UserTypeEntity()->addField($entityId, $field);
        }
        
        return true;
    }

    public function down(){
        $helper = new HelperManager();
        return true;
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
