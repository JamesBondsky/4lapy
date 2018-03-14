<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class StoreShipmentFields20180310213227 extends SprintMigrationBase
{
    const ENTITY_ID = 'CAT_STORE';

    protected $description = 'Добавление пользовательских полей, связанных со сроком поставки, для складов';

    protected $fields = [
        'UF_SHIPMENT_TILL_11' => [
            'USER_TYPE_ID' => 'week_day',
            'XML_ID' => 'UF_SHIPMENT_TILL_11',
            'SORT' => 1000,
            'MULTIPLE' => 'Y',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'Y',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Отгрузка до 11:00',
                'en' => 'Shipment till 11:00',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Отгрузка до 11:00',
                'en' => 'Shipment till 11:00',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Отгрузка до 11:00',
                'en' => 'Shipment till 11:00',
            ],
        ],
        'UF_SHIPMENT_TILL_13' => [
            'USER_TYPE_ID' => 'week_day',
            'XML_ID' => 'UF_SHIPMENT_TILL_13',
            'SORT' => 1100,
            'MULTIPLE' => 'Y',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'Y',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Отгрузка до 13:00',
                'en' => 'Shipment till 13:00',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Отгрузка до 13:00',
                'en' => 'Shipment till 13:00',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Отгрузка до 13:00',
                'en' => 'Shipment till 13:00',
            ],
        ],
        'UF_SHIPMENT_TILL_18' => [
            'USER_TYPE_ID' => 'week_day',
            'XML_ID' => 'UF_SHIPMENT_TILL_18',
            'SORT' => 1200,
            'MULTIPLE' => 'Y',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'Y',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Отгрузка до 18:00',
                'en' => 'Shipment till 18:00',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Отгрузка до 18:00',
                'en' => 'Shipment till 18:00',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Отгрузка до 18:00',
                'en' => 'Shipment till 18:00',
            ],
        ],
        'UF_DELIVERY_TIME' => [
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_DELIVERY_TIME',
            'SORT' => 1300,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'Y',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Срок поставки',
                'en' => 'Delivery time',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Срок поставки',
                'en' => 'Delivery time',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Срок поставки',
                'en' => 'Delivery time',
            ],
            'SETTINGS' => [
                'DEFAULT_VALUE' => 1,
            ],
        ],
    ];

    public function up(): bool
    {
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        foreach ($this->fields as $name => $field) {
            $res = $userTypeEntityHelper->addUserTypeEntityIfNotExists(
                static::ENTITY_ID,
                $name,
                $field
            );

            if (!$res) {
                $this->log()->error('Ошибка при создании пользовательского поля ' . $name);

                return false;
            }

            $this->log()->info('Пользовательское поле ' . $name . ' создано');
        }

        return true;
    }

    public function down(): bool
    {
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        foreach ($this->fields as $name => $field) {
            if (!$userTypeEntityHelper->deleteUserTypeEntityIfExists(static::ENTITY_ID, $name)) {
                $this->log()->warning('Ошибка при удалении пользовательского поля ' . $name);
            } else {
                $this->log()->info('Пользовательское поле ' . $name . ' удалено');
            }
        }
        return true;
    }
}
