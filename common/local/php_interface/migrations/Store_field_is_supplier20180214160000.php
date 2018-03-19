<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class Store_field_is_supplier20180214160000 extends SprintMigrationBase
{
    const ENTITY_ID = 'CAT_STORE';
    const FIELD_NAME = 'UF_IS_SUPPLIER';

    protected $description = 'Добавление пользовательского поля для складов: UF_IS_SUPPLIER';

    public function up()
    {
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        $res = $userTypeEntityHelper->addUserTypeEntityIfNotExists(
            static::ENTITY_ID,
            static::FIELD_NAME,
            [
                'USER_TYPE_ID' => 'boolean',
                'XML_ID' => static::FIELD_NAME,
                'SORT' => 700,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'Y',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'EDIT_FORM_LABEL' => [
                    'ru' => 'Склад поставщика',
                    'en' => 'Склад поставщика',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Склад поставщика',
                    'en' => 'Склад поставщика',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Склад поставщика',
                    'en' => 'Склад поставщика',
                ],
            ]
        );

        if ($res) {
            $this->log()->info('Пользовательское поле ' . static::FIELD_NAME . ' создано');
        } else {
            $this->log()->error('Ошибка при создании пользовательского поля ' . static::FIELD_NAME);

            return false;
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
