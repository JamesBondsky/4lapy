<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Sprint\Migration\Helpers\HlblockHelper;
use CUserFieldEnum;

class HLBlockForgotBasketCreate20180918001803 extends SprintMigrationBase
{
    protected $description = 'Создание таблицы для хранения заданий для отправки напоминаний о забытых корзинах';

    const HL_BLOCK_NAME = 'ForgotBasket';

    protected $hlBlockData = [
        'NAME'       => self::HL_BLOCK_NAME,
        'TABLE_NAME' => 'b_hlbd_forgot_basket',
        'LANG'       => [
            'ru' => [
                'NAME' => 'Забытые корзины',
            ],
        ],
    ];

    protected $fields = [
        [
            'FIELD_NAME'        => 'UF_USER_ID',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_USER_ID',
            'SORT'              => 10,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'Y',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'ID пользователя',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'ID пользователя',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'ID пользователя',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_DATE_UPDATE',
            'USER_TYPE_ID'      => 'datetime',
            'XML_ID'            => 'UF_DATE_UPDATE',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'Y',
            'SETTINGS'          => [
                'DEFAULT_VALUE' => [
                    'TYPE'  => 'NOW',
                    'VALUE' => '',
                ],
                'USE_SECOND'    => 'Y',
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Дата изменения',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Дата изменения',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Дата изменения',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_DATE_EXEC',
            'USER_TYPE_ID'      => 'datetime',
            'XML_ID'            => 'UF_DATE_EXEC',
            'SORT'              => 30,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'Y',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Дата последнего выполнения задания',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Дата последнего выполнения задания',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Дата последнего выполнения задания',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_TASK_TYPE',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_TASK_TYPE',
            'SORT'              => 40,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'Y',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Тип задания',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Тип задания',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Тип задания',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_ACTIVE',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => 'UF_ACTIVE',
            'SORT'              => 50,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'Y',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Активно',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Активно',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Активно',
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
            ALTER TABLE `b_hlbd_forgot_basket`
            ADD CONSTRAINT UNIQUE `UC_USER_ID_TYPE` (`UF_USER_ID`, `UF_TASK_TYPE`),
            ADD INDEX `IX_USER_ID` (`UF_USER_ID`)'
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
