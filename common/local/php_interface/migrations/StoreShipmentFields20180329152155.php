<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Sprint\Migration\Helpers\HlblockHelper;
use Sprint\Migration\Helpers\UserTypeEntityHelper;

class StoreShipmentFields20180329152155 extends SprintMigrationBase
{
    const ENTITY_ID = 'CAT_STORE';

    protected $description = 'Изменение поля "срок поставки" у складов';

    protected $fields = [
        'UF_DELIVERY_TIME' => [
            'FIELD_NAME' => 'UF_DELIVERY_TIME',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_DELIVERY_TIME',
            'SORT' => 1300,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
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

    protected $oldFields = [
        'UF_DELIVERY_TIME' => [
            'FIELD_NAME' => 'UF_DELIVERY_TIME',
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

    public function up()
    {
        foreach ($this->fields as $field) {
            if (!$this->deleteField(static::ENTITY_ID, $field['FIELD_NAME'])) {
                return false;
            }

            if (!$this->addField(static::ENTITY_ID, $field)) {
                return false;
            }
        }

        return true;
    }

    public function down()
    {
        foreach ($this->oldFields as $field) {
            if (!$this->deleteField(static::ENTITY_ID, $field['FIELD_NAME'])) {
                return false;
            }

            if (!$this->addField(static::ENTITY_ID, $field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $entityId
     * @param $fieldName
     * @return bool
     */
    protected function deleteField($entityId, $fieldName): bool
    {
        if (!$this->userTypeEntityHelper->getUserTypeEntity($entityId, $fieldName)) {
            return true;
        }

        if ($this->userTypeEntityHelper->deleteUserTypeEntityIfExists($entityId, $fieldName)) {
            $this->log()->info(sprintf(
                'Удалено поле %s из HL-блока %s',
                $fieldName,
                $entityId
            ));
        } else {
            $this->log()->error(sprintf(
                'Ошибка при удалении поля %s из HL-блока %s',
                $fieldName,
                $entityId
            ));
            return false;
        }

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
            $enum = new \CUserFieldEnum();
            if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                $this->log()->info(sprintf('Добавлены значения для поля %s', $field['FIELD_NAME']));
            } else {
                $this->log()->error(sprintf('Не удалось добавить значения для поля %s', $field['FIELD_NAME']));
            }
        }

        return true;
    }
}
