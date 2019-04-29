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
        $fieldName = 'UF_CHECK_DAYS';
        $ruName = 'Разница дней для формирования заказа';
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
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                [
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

        $fieldName = 'UF_DATE_START';
        $userTypeEntityHelper->deleteUserTypeEntityIfExists($entityId, $fieldName);

        $GLOBALS['DB']->Query('ALTER TABLE `'.static::TABLE_NAME.'` drop index `UF_ORDER_ID`;', true);

        return true;
    }

    public function down()
    {
        $this->getHelper()->Hlblock()->deleteHlblockIfExists(self::HL_NAME);
        return true;
    }

}
