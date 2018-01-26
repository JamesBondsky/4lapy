<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\App\Application;
use FourPaws\Console\Command\MigrateClear;
use FourPaws\Console\ConsoleApp;
use Sprint\Migration\Helpers\HlblockHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class HLBlock_cities_20171206125709 extends SprintMigrationBase
{
    protected $description = 'Создание HL-блока городов';

    const HL_BLOCK_NAME = 'Cities';

    protected $hlBlockData = [
        'NAME'       => self::HL_BLOCK_NAME,
        'TABLE_NAME' => 'b_hlbd_cities',
        'LANG'       => [
            'ru' => [
                'NAME' => 'Города',
            ],
            'en' => [
                'NAME' => 'Cities',
            ],
        ],
    ];

    protected $fields = [
        [
            'FIELD_NAME'        => 'UF_PHONE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_PHONE',
            'SORT'              => 10,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Телефон',
                'en' => 'Phone',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Телефон',
                'en' => 'Phone',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Телефон',
                'en' => 'Phone',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_NAME',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_NAME',
            'SORT'              => 20,
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
            'FIELD_NAME'        => 'UF_ACTIVE',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => 'UF_ACTIVE',
            'SORT'              => 30,
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
            'FIELD_NAME'        => 'UF_SORT',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_SORT',
            'SORT'              => 40,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Сортировка',
                'en' => 'Sort',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Сортировка',
                'en' => 'Sort',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Сортировка',
                'en' => 'Sort',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_DELIVERY_TEXT',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_DELIVERY_TEXT',
            'SORT'              => 50,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Текст для доставки',
                'en' => 'Delivery text',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Текст для доставки',
                'en' => 'Delivery text',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Текст для доставки',
                'en' => 'Delivery text',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_DEFAULT',
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => 'UF_DEFAULT',
            'SORT'              => 60,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'По умолчанию',
                'en' => 'Is default',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'По умолчанию',
                'en' => 'Is default',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'По умолчанию',
                'en' => 'Is default',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_LOCATION',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_LOCATION',
            'SORT'              => 500,
            'MULTIPLE'          => 'Y',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Местоположения',
                'en' => 'Locations',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Местоположения',
                'en' => 'Locations',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Местоположения',
                'en' => 'Locations',
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
            if ($userTypeEntityHelper->addUserTypeEntityIfNotExists(
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
