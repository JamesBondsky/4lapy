<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Sprint\Migration\Helpers\HlblockHelper;

class HLBlockDeliveryScheduleResultUpdate20180505231326 extends SprintMigrationBase
{
    protected $description = 'Обновление таблицы для хранения расчетов по графикам поставок';

    protected const HL_BLOCK_NAME = 'DeliveryScheduleResult';

    protected $hlBlockData = [
        'NAME' => self::HL_BLOCK_NAME,
        'TABLE_NAME' => 'b_hlbd_delivery_schedule_result',
        'LANG' => [
            'ru' => [
                'NAME' => 'Расчеты по графикам поставок',
            ],
        ],
    ];

    protected $oldFields = [
        [
            'FIELD_NAME' => 'UF_DAYS',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_DAYS',
            'SORT' => 10,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Кол-во дней',
                'en' => 'Days',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Кол-во дней',
                'en' => 'Days',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Кол-во дней',
                'en' => 'Days',
            ],
        ],
    ];

    protected $fields = [
        [
            'FIELD_NAME' => 'UF_DAYS_11',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_DAYS_11',
            'SORT' => 10,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 11)',
                'en' => 'Days with shipment till 11',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 11)',
                'en' => 'Days with shipment till 11',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 11)',
                'en' => 'Days with shipment till 11',
            ],
        ],
        [
            'FIELD_NAME' => 'UF_DAYS_13',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_DAYS_13',
            'SORT' => 10,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 13)',
                'en' => 'Days with shipment till 13',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 13)',
                'en' => 'Days with shipment till 13',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 13)',
                'en' => 'Days with shipment till 13',
            ],
        ],
        [
            'FIELD_NAME' => 'UF_DAYS_18',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_DAYS_18',
            'SORT' => 10,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 18)',
                'en' => 'Days with shipment till 18',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 18)',
                'en' => 'Days with shipment till 18',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 18)',
                'en' => 'Days with shipment till 18',
            ],
        ],
        [
            'FIELD_NAME' => 'UF_DAYS_24',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_DAYS_24',
            'SORT' => 10,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 00)',
                'en' => 'Days with shipment till 00',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 00)',
                'en' => 'Days with shipment till 00',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Кол-во дней (отгрузка до 00)',
                'en' => 'Days with shipment till 00',
            ],
        ],
    ];

    public function up()
    {
        /** @var HlblockHelper $hlBlockHelper */
        $hlBlockHelper = $this->getHelper()->Hlblock();

        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if (!$hlBlockId = $hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME)) {
            $this->log()->error('HL-блок ' . static::HL_BLOCK_NAME . ' не найден');

            return false;
        }

        $entityId = 'HLBLOCK_' . $hlBlockId;

        foreach ($this->oldFields as $field) {
            if (!$userTypeEntityHelper->deleteUserTypeEntityIfExists($entityId, $field['FIELD_NAME'])) {
                $this->log()->error(
                    'Ошибка при удалении поля ' . $field['FIELD_NAME'] . ' из HL-блока ' . self::HL_BLOCK_NAME
                );
            }
        }

        foreach ($this->fields as $field) {
            if ($fieldId = $userTypeEntityHelper->addUserTypeEntityIfNotExists(
                $entityId,
                $field['FIELD_NAME'],
                $field
            )) {
                $this->log()->info(
                    'Добавлено поле ' . $field['FIELD_NAME'] . ' в HL-блок ' . self::HL_BLOCK_NAME
                );
            } else {
                $this->log()->error(
                    'Ошибка при добавлении поля ' . $field['FIELD_NAME'] . ' в HL-блок ' . self::HL_BLOCK_NAME
                );

                return false;
            }
        }

        return true;
    }

    public function down()
    {
        /** @var HlblockHelper $hlBlockHelper */
        $hlBlockHelper = $this->getHelper()->Hlblock();

        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if (!$hlBlockId = $hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME)) {
            $this->log()->error('HL-блок ' . static::HL_BLOCK_NAME . ' не найден');

            return false;
        }

        $entityId = 'HLBLOCK_' . $hlBlockId;

        foreach ($this->fields as $field) {
            if (!$userTypeEntityHelper->deleteUserTypeEntityIfExists($entityId, $field['FIELD_NAME'])) {
                $this->log()->error(
                    'Ошибка при удалении поля ' . $field['FIELD_NAME'] . ' из HL-блока ' . self::HL_BLOCK_NAME
                );
            }
        }

        foreach ($this->oldFields as $field) {
            if ($fieldId = $userTypeEntityHelper->addUserTypeEntityIfNotExists(
                $entityId,
                $field['FIELD_NAME'],
                $field
            )) {
                $this->log()->info(
                    'Добавлено поле ' . $field['FIELD_NAME'] . ' в HL-блок ' . self::HL_BLOCK_NAME
                );
            } else {
                $this->log()->error(
                    'Ошибка при добавлении поля ' . $field['FIELD_NAME'] . ' в HL-блок ' . self::HL_BLOCK_NAME
                );

                return false;
            }
        }

        return true;
    }
}
