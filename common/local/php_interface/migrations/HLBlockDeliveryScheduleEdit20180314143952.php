<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Sprint\Migration\Helpers\HlblockHelper;
use Sprint\Migration\Helpers\UserTypeEntityHelper;
use CUserFieldEnum;

class HLBlockDeliveryScheduleEdit20180314143952 extends SprintMigrationBase
{
    protected $description = 'Изменение свойств HL-блока "график поставок"';

    protected const HL_BLOCK_NAME = 'DeliverySchedule';

    protected $fields = [
        [
            'FIELD_NAME'        => 'UF_WEEK_NUMBER',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_WEEK_NUMBER',
            'SORT'              => 80,
            'MULTIPLE'          => 'Y',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Номер недели',
                'en' => 'Week number',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Номер недели',
                'en' => 'Week number',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Номер недели',
                'en' => 'Week number',
            ],
        ],
    ];

    protected $oldFields = [
        [
            'FIELD_NAME'        => 'UF_WEEK_NUMBER',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_WEEK_NUMBER',
            'SORT'              => 80,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Номер недели',
                'en' => 'Week number',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Номер недели',
                'en' => 'Week number',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Номер недели',
                'en' => 'Week number',
            ],
        ],
    ];

    protected $fieldsToDelete = [];

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
        if (!$hlBlockId = $this->hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME)) {
            $this->log()->error(sprintf('HL-блок %s не найден ', static::HL_BLOCK_NAME));
            return false;
        }
        $entityId = 'HLBLOCK_' . $hlBlockId;

        foreach ($this->fieldsToDelete as $field) {
            if (!$this->deleteField($entityId, $field['FIELD_NAME'])) {
                return false;
            }
        }

        foreach ($this->fields as $field) {
            if (!$this->deleteField($entityId, $field['FIELD_NAME'])) {
                return false;
            }

            if (!$this->addField($entityId, $field)) {
                return false;
            }
        }

        return true;
    }

    public function down()
    {
        if (!$hlBlockId = $this->hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME)) {
            $this->log()->error(sprintf('HL-блок %s не найден ', static::HL_BLOCK_NAME));
            return false;
        }
        $entityId = 'HLBLOCK_' . $hlBlockId;

        foreach ($this->fieldsToDelete as $field) {
            if (!$this->addField($entityId, $field)) {
                return false;
            }
        }

        foreach ($this->oldFields as $field) {
            if (!$this->deleteField($entityId, $field['FIELD_NAME'])) {
                return false;
            }

            if (!$this->addField($entityId, $field)) {
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
            $enum = new CUserFieldEnum();
            if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                $this->log()->info(sprintf('Добавлены значения для поля %s', $field['FIELD_NAME']));
            } else {
                $this->log()->error(sprintf('Не удалось добавить значения для поля %s', $field['FIELD_NAME']));
            }
        }

        return true;
    }
}

