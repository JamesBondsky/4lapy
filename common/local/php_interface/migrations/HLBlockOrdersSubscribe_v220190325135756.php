<?php

namespace Sprint\Migration;


class HLBlockOrdersSubscribe_v220190325135756 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    const HL_NAME = 'OrderSubscribe';
    const TABLE_NAME = '4lp_order_subscribe';

    protected $description = 'Highloadblock для подписок на доставку';

    public function up()
    {
        $hlBlockHelper = $this->getHelper()->Hlblock();

        $hlBlockId = $hlBlockHelper->addHlblockIfNotExists(
            [
                'NAME' => static::HL_NAME,
                'TABLE_NAME' => static::TABLE_NAME,
                'LANG' => [
                    'ru' => [
                        'NAME' => 'Подписка на доставку: подписки',
                    ],
                ],
            ]
        );
        $entityId  = 'HLBLOCK_'.$hlBlockId;

        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        $sort = 0;

        // ---
        $fieldName = 'UF_USER_ID';
        $ruName = 'ID пользователя';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'integer',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'Y',
                'SETTINGS' => [
                    'SIZE' => 20,
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_DEL_TYPE';
        $ruName = 'ID службы доставки';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'integer',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'Y',
                'SETTINGS' => [
                    'SIZE' => 20,
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_FREQUENCY';
        $ruName = 'Периодичность';
        $sort += 100;

        $fieldId = $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'enumeration',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DISPLAY' => 'LIST',
                    'CAPTION_NO_VALUE' => 'выберите',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );
        (new \CUserFieldEnum())->SetEnumValues(
            $fieldId,
            [
                'n1' => [
                    'XML_ID' => 'WEEK_1',
                    'VALUE' => 'Раз в неделю',
                    'SORT' => 100,
                ],
                'n2' => [
                    'XML_ID' => 'WEEK_2',
                    'VALUE' => 'Раз в две недели',
                    'SORT' => 200,
                ],
                'n3' => [
                    'XML_ID' => 'WEEK_3',
                    'VALUE' => 'Раз в три недели',
                    'SORT' => 300,
                ],
                'n4' => [
                    'XML_ID' => 'MONTH_1',
                    'VALUE' => 'Раз в месяц',
                    'SORT' => 400,
                ],
                'n5' => [
                    'XML_ID' => 'MONTH_2',
                    'VALUE' => 'Раз в два месяца',
                    'SORT' => 500,
                ],
                'n6' => [
                    'XML_ID' => 'MONTH_3',
                    'VALUE' => 'Раз в три месяца',
                    'SORT' => 600,
                ],
            ]
        );

        // ---
        $fieldName = 'UF_DELIVERY_TIME';
        $ruName = 'Время доставки';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_DEL_PLACE';
        $ruName = 'Адрес доставки';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'string',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_DEL_DAY';
        $ruName = 'День доставки';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'week_day',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_LOCATION';
        $ruName = 'Местоположение';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'sale_location',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_ACTIVITY';
        $ruName = 'Активность';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'boolean',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => 1,
                    'DISPLAY' => 'CHECKBOX',
                    'LABEL' => [
                        0 => '',
                        1 => '',
                    ],
                    'LABEL_CHECKBOX' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_SKIP_DEL';
        $ruName = 'Пропустить следующую доставку';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'boolean',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => 0,
                    'DISPLAY' => 'CHECKBOX',
                    'LABEL' => [
                        0 => '',
                        1 => '',
                    ],
                    'LABEL_CHECKBOX' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_LAST_ORDER';
        $ruName = 'ID последнего заказа';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'integer',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => '',
                'EDIT_IN_LIST' => '',
                'IS_SEARCHABLE' => 'Y',
                'SETTINGS' => [
                    'SIZE' => 20,
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_NEXT_DEL';
        $ruName = 'Дата следующей доставки';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => [
                        'TYPE' => '',
                        'VALUE' => '',
                    ],
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_DATE_CREATE';
        $ruName = 'Дата создания';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => [
                        'TYPE' => '',
                        'VALUE' => '',
                    ],
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_DATE_UPDATE';
        $ruName = 'Дата изменения';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => [
                        'TYPE' => '',
                        'VALUE' => '',
                    ],
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        // ---
        $fieldName = 'UF_DATE_UPDATE';
        $ruName = 'Дата последней проверки';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => [
                        'TYPE' => '',
                        'VALUE' => '',
                    ],
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => $ruName,
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => $ruName,
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => '',
                ],
            ]
        );

        return true;
    }

    public function down()
    {
        $this->getHelper()->Hlblock()->deleteHlblockIfExists(self::HL_NAME);
        return true;
    }

}
