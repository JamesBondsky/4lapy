<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Catalog\StoreTable;
use CCatalogStore;

class TpzTimeFieldForStores20190222165555 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = 'Поле время, до которого сегодня можно оформить заказ, чтобы он попал в поставку.';

    const ENTITY_ID = 'CAT_STORE';

    protected $fields = [
        'UF_STORE_ORDER_TIME' => [
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_STORE_ORDER_TIME',
            'SORT' => 1300,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'Y',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Время до которого необходимо оформить заказ',
                'en' => 'Время до которого необходимо оформить заказ',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Время до которого необходимо оформить заказ',
                'en' => 'Время до которого необходимо оформить заказ',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Время до которого необходимо оформить заказ',
                'en' => 'Время до которого необходимо оформить заказ',
            ],
            'SETTINGS' => [
                'DEFAULT_VALUE' => '13:00:00',
            ],
        ],
    ];

    public function up()
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
        }
        $dbResult = CCatalogStore::GetList([], [], false, false, array('ID'));
        while ($store = $dbResult->Fetch()) {
            StoreTable::update(
                $store['ID'],
                [
                    'fields' =>
                        [
                            'UF_STORE_ORDER_TIME' => '13:00:00',
                            'UF_DELIVERY_TIME' => '1'
                        ]
                ]
            );
        }

        return true;
    }

    public function down()
    {
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        foreach ($this->fields as $name => $field) {
            if (!$userTypeEntityHelper->deleteUserTypeEntityIfExists(static::ENTITY_ID, $name)) {
                $this->log()->warning('Ошибка при удалении пользовательского поля ' . $name);
            }
        }
        return true;
    }

}