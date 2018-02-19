<?php

namespace Sprint\Migration;

class HLBlockOrdersSubscribe20180215180000 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = 'Highloadblock для подписок на заказы';
    
    public function up()
    {
        $hlBlockHelper = $this->getHelper()->Hlblock();

        $hlBlockId = $hlBlockHelper->addHlblockIfNotExists(
            [
                'NAME' => 'OrderSubscribe',
                'TABLE_NAME' => '4lp_order_subscribe',
                'LANG' => [
                    'ru' => [
                        'NAME' => 'Подписка на заказ',
                    ],
                ],
            ]
        );
        $entityId  = 'HLBLOCK_'.$hlBlockId;

        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        $sort = 0;

        // ---
        $fieldName = 'UF_ORDER_ID';
        $ruName = 'ID заказа';
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
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
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
                        'TYPE' => 'NOW',
                        'VALUE' => '',
                    ],
                    'USE_SECOND' => 'Y',
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
        $fieldName = 'UF_DATE_START';
        $ruName = 'Дата первой поставки';
        $sort += 100;
        $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'date',
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
                        'TYPE' => 'NOW',
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
        $fieldId = $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldName,
            [
                'FIELD_NAME' => $fieldName,
                'USER_TYPE_ID' => 'enumeration',
                'XML_ID' => '',
                'SORT' => $sort,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
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
                    'XML_ID' => 'TIME_1',
                    'VALUE' => '10:00—16:00',
                    'SORT' => 100,
                ],
                'n2' => [
                    'XML_ID' => 'TIME_2',
                    'VALUE' => '16:00—18:00',
                    'SORT' => 200,
                ],
                'n3' => [
                    'XML_ID' => 'TIME_3',
                    'VALUE' => '18:00—20:00',
                    'SORT' => 300,
                ],
            ]
        );

        // ---
        $fieldName = 'UF_ACTIVE';
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

        return true;
    }
    
    public function down()
    {

    }
}
