<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Sprint\Migration\Helpers\HlblockHelper;
use CUserFieldEnum;

class HLBlock_delivery_schedule_create20171222171606 extends SprintMigrationBase
{
    protected $description = 'Создание HL-блока "график поставок"';

    const HL_BLOCK_NAME = 'DeliverySchedule';

    protected $hlBlockData = [
        'NAME'       => self::HL_BLOCK_NAME,
        'TABLE_NAME' => 'b_hlbd_delivery_schedule',
        'LANG'       => [
            'ru' => [
                'NAME' => 'График поставок',
            ],
            'en' => [
                'NAME' => 'Delivery schedule',
            ],
        ],
    ];

    protected $fields = [
        [
            'FIELD_NAME'        => 'UF_NAME',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_NAME',
            'SORT'              => 10,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Название',
                'en' => 'Name',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Название',
                'en' => 'Name',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Название',
                'en' => 'Name',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_SENDER',
            'USER_TYPE_ID'      => 'catalog_store_list',
            'XML_ID'            => 'UF_SENDER',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Склад отправителя',
                'en' => 'Sender store',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Склад отправителя',
                'en' => 'Sender store',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Склад отправителя',
                'en' => 'Sender store',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_RECEIVER',
            'USER_TYPE_ID'      => 'catalog_store_list',
            'XML_ID'            => 'UF_RECEIVER',
            'SORT'              => 30,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Склад получателя',
                'en' => 'Receiver store',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Склад получателя',
                'en' => 'Receiver store',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Склад получателя',
                'en' => 'Receiver store',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_ACTIVE_FROM',
            'USER_TYPE_ID'      => 'datetime',
            'XML_ID'            => 'UF_ACTIVE_FROM',
            'SORT'              => 40,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          => [
                'DEFAULT_VALUE' => [
                    'TYPE'  => 'NOW',
                    'VALUE' => '',
                ],
                'USE_SECOND'    => 'Y',
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Активен с',
                'en' => 'Active from',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Активен с',
                'en' => 'Active from',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Активен с',
                'en' => 'Active from',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_ACTIVE_TO',
            'USER_TYPE_ID'      => 'datetime',
            'XML_ID'            => 'UF_ACTIVE_TO',
            'SORT'              => 50,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          => [
                'USE_SECOND' => 'Y',
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Активен по',
                'en' => 'Active to',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Активен по',
                'en' => 'Active to',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Активен по',
                'en' => 'Active to',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_ACTIVE',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => 'UF_ACTIVE',
            'SORT'              => 60,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Активность',
                'en' => 'Active',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Активность',
                'en' => 'Active',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Активность',
                'en' => 'Active',
            ],
            'SETTINGS'          => [
                'DEFAULT_VALUE' => true,
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_TYPE',
            'USER_TYPE_ID'      => 'enumeration',
            'XML_ID'            => 'UF_TYPE',
            'SORT'              => 70,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Тип графика',
                'en' => 'Schedule type',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Тип графика',
                'en' => 'Schedule type',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Тип графика',
                'en' => 'Schedule type',
            ],
            'ENUMS' => [
                'n1' => [
                    'XML_ID' => '1',
                    'VALUE' => 'Еженедельный'
                ],
                'n2' => [
                    'XML_ID' => '2',
                    'VALUE' => 'По определенным неделям',
                ],
                'n3' => [
                    'XML_ID' => '8',
                    'VALUE' => 'Ручной'
                ]
            ]
        ],
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
        [
            'FIELD_NAME'        => 'UF_DAY_OF_WEEK',
            'USER_TYPE_ID'      => 'enumeration',
            'XML_ID'            => 'UF_DAY_OF_WEEK',
            'SORT'              => 90,
            'MULTIPLE'          => 'Y',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'День недели',
                'en' => 'Day of week',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'День недели',
                'en' => 'Day of week',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'День недели',
                'en' => 'Day of week',
            ],
            'ENUMS'             => [
                'n1' => [
                    'XML_ID' => 'Monday',
                    'VALUE'  => 'Понедельник',
                ],
                'n2' => [
                    'XML_ID' => 'Tuesday',
                    'VALUE'  => 'Вторник',
                ],
                'n3' => [
                    'XML_ID' => 'Wednesday',
                    'VALUE'  => 'Среда',
                ],
                'n4' => [
                    'XML_ID' => 'Thursday',
                    'VALUE'  => 'Четверг',
                ],
                'n5' => [
                    'XML_ID' => 'Friday',
                    'VALUE'  => 'Пятница',
                ],
                'n6' => [
                    'XML_ID' => 'Saturday',
                    'VALUE'  => 'Суббота',
                ],
                'n7' => [
                    'XML_ID' => 'Sunday',
                    'VALUE'  => 'Воскресенье',
                ],
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_DELIVERY_NUMBER',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_DELIVERY_NUMBER',
            'SORT'              => 100,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Номер поставки',
                'en' => 'Delivery number',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Номер поставки',
                'en' => 'Delivery number',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Номер поставки',
                'en' => 'Delivery number',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_DELIVERY_DATE',
            'USER_TYPE_ID'      => 'date',
            'XML_ID'            => 'UF_DELIVERY_DATE',
            'SORT'              => 110,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Дата поставки',
                'en' => 'Delivery date',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Дата поставки',
                'en' => 'Delivery date',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Дата поставки',
                'en' => 'Delivery date',
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
