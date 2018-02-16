<?php

namespace Sprint\Migration;

class HLBlock_orders_subscribe20180215180000 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
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
        $fieldName = 'UF_PERIODICITY';
        $ruName = 'Периодичность';
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
