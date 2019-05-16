<?php

namespace Sprint\Migration;


class HLBlockOrdersSubscribeItems2019032513575620190325160129 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    const HL_NAME = 'OrderSubscribeItems';
    const TABLE_NAME = '4lp_order_subscribe_items';

    protected $description = 'Highloadblock для товаров для подписки на доставку';

    public function up()
    {
        $hlBlockHelper = $this->getHelper()->Hlblock();

        $hlBlockId = $hlBlockHelper->addHlblockIfNotExists(
            [
                'NAME' => static::HL_NAME,
                'TABLE_NAME' => static::TABLE_NAME,
                'LANG' => [
                    'ru' => [
                        'NAME' => 'Подписка на доставку: товары',
                    ],
                ],
            ]
        );
        $entityId  = 'HLBLOCK_'.$hlBlockId;

        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        $sort = 0;

        // ---
        $fieldName = 'UF_SUBSCRIBE_ID';
        $ruName = 'ID подписки';
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
        $fieldName = 'UF_OFFER_ID';
        $ruName = 'ID торгового предложения';
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
        $fieldName = 'UF_QUANTITY';
        $ruName = 'Количество';
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
                    'MIN_VALUE' => 1,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => 1,
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
