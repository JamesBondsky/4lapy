<?php

namespace Sprint\Migration;


class HlBlockOrderSubscribeUpgrade20190905173304 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Создаёт новую таблицу \"Единичная подписка\" и добавляет новые интервалы периодичности";

    const HL_NAME = 'OrderSubscribeSingle';
    const TABLE_NAME = '4lp_order_subscribe_single';

    public function up(){
        $helper = new HelperManager();
        $hlBlockHelper = $this->getHelper()->Hlblock();

        $hlBlockId = $hlBlockHelper->addHlblockIfNotExists(
            [
                'NAME' => static::HL_NAME,
                'TABLE_NAME' => static::TABLE_NAME,
                'LANG' => [
                    'ru' => [
                        'NAME' => 'Подписка на доставку: единичная доставка',
                    ],
                ],
            ]
        );
        $entityId  = 'HLBLOCK_'.$hlBlockId;

        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        // ---
        $field = [
            "FIELD_NAME" => "UF_SUBSCRIBE_ID",
            "USER_TYPE_ID" => "integer",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "Y",
            "SHOW_FILTER" => "S",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "SIZE" => "20",
                "MIN_VALUE" => "0",
                "MAX_VALUE" => "0",
                "DEFAULT_VALUE" => "",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "ID подписки на доставку",
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "ID подписки на доставку",
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "ID подписки на доставку",
            ],
            "ERROR_MESSAGE" => [
                "ru" => "",
            ],
            "HELP_MESSAGE" => [
                "ru" => "",
            ],
        ];

        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $field['FIELD_NAME'],
            $field
        );

        // ---
        $field = [
            "FIELD_NAME" => "UF_ITEMS",
            "USER_TYPE_ID" => "string",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "Y",
            "SHOW_FILTER" => "S",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "SIZE" => "20",
                "ROWS" => "5",
                "REGEXP" => "",
                "MIN_LENGTH" => "0",
                "MAX_LENGTH" => "0",
                "DEFAULT_VALUE" => "",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Товары",
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Товары",
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Товары",
            ],
            "ERROR_MESSAGE" => [
                "ru" => "",
            ],
            "HELP_MESSAGE" => [
                "ru" => "",
            ],
        ];

        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $field['FIELD_NAME'],
            $field
        );

        // ---
        $field = [
            "FIELD_NAME" => "UF_DATE_CREATE",
            "USER_TYPE_ID" => "datetime",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "Y",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "DEFAULT_VALUE" => "Array",
                "USE_SECOND" => "Y",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Дата создания",
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Дата создания",
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Дата создания",
            ],
            "ERROR_MESSAGE" => [
                "ru" => "",
            ],
            "HELP_MESSAGE" => [
                "ru" => "",
            ],
        ];

        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $field['FIELD_NAME'],
            $field
        );

        // ---
        $field = [
            "FIELD_NAME" => "UF_ACTIVE",
            "USER_TYPE_ID" => "boolean",
            "XML_ID" => "",
            "SORT" => "100",
            "MULTIPLE" => "N",
            "MANDATORY" => "N",
            "SHOW_FILTER" => "N",
            "SHOW_IN_LIST" => "Y",
            "EDIT_IN_LIST" => "Y",
            "IS_SEARCHABLE" => "N",
            "SETTINGS" => [
                "DEFAULT_VALUE" => "0",
                "DISPLAY" => "CHECKBOX",
                "LABEL" => "Array",
                "LABEL_CHECKBOX" => "",
            ],
            "EDIT_FORM_LABEL" => [
                "ru" => "Активность",
            ],
            "LIST_COLUMN_LABEL" => [
                "ru" => "Активность",
            ],
            "LIST_FILTER_LABEL" => [
                "ru" => "Активность",
            ],
            "ERROR_MESSAGE" => [
                "ru" => "",
            ],
            "HELP_MESSAGE" => [
                "ru" => "",
            ],
        ];

        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $field['FIELD_NAME'],
            $field
        );

        $values = [
            [
                'XML_ID' => 'WEEK_7',
                'VALUE' => 'Раз в семь недель',
            ],
            [
                'XML_ID' => 'WEEK_8',
                'VALUE' => 'Раз в восемь недель',
            ],
            [
                'XML_ID' => 'WEEK_9',
                'VALUE' => 'Раз в девять недель',
            ],
            [
                'XML_ID' => 'WEEK_10',
                'VALUE' => 'Раз в десять недель',
            ],
            [
                'XML_ID' => 'WEEK_11',
                'VALUE' => 'Раз в одиннадцать недель',
            ],
            [
                'XML_ID' => 'WEEK_12',
                'VALUE' => 'Раз в двенадцать недель',
            ],
        ];

        $fieldId = \CUserTypeEntity::GetList([], ['FIELD_NAME' => 'UF_FREQUENCY', 'ENTITY_ID' => 'HLBLOCK_43'])->Fetch()['ID'];

        $enum = new \CUserFieldEnum();
        if ($enum->SetEnumValues($fieldId, $values)) {
            $this->log()->info(sprintf('Добавлены значения для поля %s', $field['FIELD_NAME']));
        } else {
            $this->log()->error(sprintf('Не удалось добавить значения для поля %s', $field['FIELD_NAME']));
        }


        return true;
    }

    public function down(){
        $helper = new HelperManager();
        return true;
    }

}
