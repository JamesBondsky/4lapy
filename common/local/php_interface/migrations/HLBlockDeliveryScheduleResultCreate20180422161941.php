<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Sprint\Migration\Helpers\HlblockHelper;
use CUserFieldEnum;

class HLBlockDeliveryScheduleResultCreate20180422161941 extends SprintMigrationBase
{
    protected $description = 'Создание таблицы для хранения расчетов по графикам поставок';

    const HL_BLOCK_NAME = 'DeliveryScheduleResult';

    protected $hlBlockData = [
        'NAME'       => self::HL_BLOCK_NAME,
        'TABLE_NAME' => 'b_hlbd_delivery_schedule_result',
        'LANG'       => [
            'ru' => [
                'NAME' => 'Расчеты по графикам поставок',
            ],
        ],
    ];

    protected $fields = [
        [
            'FIELD_NAME'        => 'UF_RECEIVER',
            'USER_TYPE_ID'      => 'catalog_store_list',
            'XML_ID'            => 'UF_RECEIVER',
            'SORT'              => 10,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Склад-получатель',
                'en' => 'Receiver store',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Склад-получатель',
                'en' => 'Receiver store',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Склад-получатель',
                'en' => 'Receiver store',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_SENDER',
            'USER_TYPE_ID'      => 'catalog_store_list',
            'XML_ID'            => 'UF_SENDER',
            'SORT'              => 10,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Склад-отправитель',
                'en' => 'Sender store',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Склад-отправитель',
                'en' => 'Sender store',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Склад-отправитель',
                'en' => 'Sender store',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_ROUTE',
            'USER_TYPE_ID'      => 'catalog_store_list',
            'XML_ID'            => 'UF_ROUTE',
            'SORT'              => 10,
            'MULTIPLE'          => 'Y',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Маршрут',
                'en' => 'Route',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Маршрут',
                'en' => 'Route',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Маршрут',
                'en' => 'Route',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_DAYS',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_DAYS',
            'SORT'              => 10,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
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

    public function up()
    {
        /** @var HlblockHelper $hlBlockHelper */
        $hlBlockHelper = $this->getHelper()->Hlblock();

        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if (!$hlBlockId = $hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME)) {
            if ($hlBlockId = $hlBlockHelper->addHlblock($this->hlBlockData)) {
                $this->log()->info('Добавлен HL-блок ' . static::HL_BLOCK_NAME);
            } else {
                $this->log()->error('Ошибка при создании HL-блока ' . static::HL_BLOCK_NAME);

                return false;
            }
        } else {
            $this->log()->info('HL-блок ' . static::HL_BLOCK_NAME . ' уже существует');
        }

        $entityId = 'HLBLOCK_' . $hlBlockId;
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

            if ($field['ENUMS']) {
                $enum = new CUserFieldEnum();
                if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                    $this->log()->info('Добавлены значения для поля ' . $field['FIELD_NAME']);
                } else {
                    $this->log()->error('Не удалось добавить значения для поля ' . $field['FIELD_NAME']);
                }
            }
        }

        Application::getConnection()->query('
                ALTER TABLE `b_hlbd_delivery_schedule_result`
                    ADD INDEX `IX_SENDER` (`UF_SENDER`),
                    ADD INDEX `IX_RECEIVER` (`UF_RECEIVER`),
                    ADD INDEX `IX_SENDER_RECEIVER` (`UF_SENDER`, `UF_RECEIVER`)'
        );

        return true;
    }

    public function down()
    {
        /** @var HlblockHelper $hlBlockHelper */
        $hlBlockHelper = $this->getHelper()->Hlblock();

        if (!$hlBlockId = $hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME)) {
            $this->log()->error('HL-блок ' . static::HL_BLOCK_NAME . ' не найден');

            return true;
        }

        if ($hlBlockHelper->deleteHlblock($hlBlockId)) {
            $this->log()->info('HL-блок ' . static::HL_BLOCK_NAME . ' удален');
        } else {
            $this->log()->error('Ошибка при удалении HL-блока ' . static::HL_BLOCK_NAME);

            return false;
        }

        return true;
    }
}
