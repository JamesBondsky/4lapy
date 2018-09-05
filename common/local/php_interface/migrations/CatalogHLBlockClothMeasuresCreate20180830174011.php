<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;

class CatalogHLBlockClothMeasuresCreate20180830174011 extends SprintMigrationBase
{
    protected $description = 'Создание HL-блока "Подбор размера одежды"';

    protected const HL_BLOCK_NAME = 'ClothingSizeSelection';

    protected const HL_BLOCK_SERVICE_NAME = 'bx.hlblock.clothingsizeselection';

    protected $hlBlockData = [
        'NAME'       => self::HL_BLOCK_NAME,
        'TABLE_NAME' => 'b_hlbd_clothing_size_selection',
        'LANG'       => [
            'ru' => [
                'NAME' => 'Подбор размера одежды',
            ],
        ],
    ];

    protected $fields = [
        [
            'FIELD_NAME'        => 'UF_CODE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_CODE',
            'SORT'              => 10,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'Y',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Код размера',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Код размера',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Код размера',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_CHEST_MIN',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_CHEST_MIN',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Обхват груди от',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Обхват груди от',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Обхват груди от',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_CHEST_MAX',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_CHEST_MAX',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Обхват груди до',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Обхват груди до',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Обхват груди до',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_NECK_MIN',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_NECK_MIN',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Обхват шеи от',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Обхват шеи от',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Обхват шеи от',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_NECK_MAX',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_NECK_MAX',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Обхват шеи до',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Обхват шеи до',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Обхват шеи до',
            ],
        ],

        [
            'FIELD_NAME'        => 'UF_BACK_MIN',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_BACK_MIN',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Длина спины от',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Длина спины от',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Длина спины от',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_BACK_MAX',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_BACK_MAX',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Длина спины до',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Длина спины до',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Длина спины до',
            ],
        ],
    ];

    protected $items = [
        [
            'UF_CODE'      => 'XS',
            'UF_BACK_MIN'  => '10',
            'UF_BACK_MAX'  => '21',
            'UF_CHEST_MIN' => '10',
            'UF_CHEST_MAX' => '31',
            'UF_NECK_MIN'  => '10',
            'UF_NECK_MAX'  => '20',
        ],
        [
            'UF_CODE'      => 'S',
            'UF_BACK_MIN'  => '22',
            'UF_BACK_MAX'  => '23',
            'UF_CHEST_MIN' => '32',
            'UF_CHEST_MAX' => '36',
            'UF_NECK_MIN'  => '21',
            'UF_NECK_MAX'  => '23',
        ],
        [
            'UF_CODE'      => 'M',
            'UF_BACK_MIN'  => '24',
            'UF_BACK_MAX'  => '28',
            'UF_CHEST_MIN' => '37',
            'UF_CHEST_MAX' => '42',
            'UF_NECK_MIN'  => '24',
            'UF_NECK_MAX'  => '27',
        ],
        [
            'UF_CODE'      => 'L',
            'UF_BACK_MIN'  => '29',
            'UF_BACK_MAX'  => '33',
            'UF_CHEST_MIN' => '43',
            'UF_CHEST_MAX' => '48',
            'UF_NECK_MIN'  => '28',
            'UF_NECK_MAX'  => '31',
        ],
        [
            'UF_CODE'      => 'XL',
            'UF_BACK_MIN'  => '34',
            'UF_BACK_MAX'  => '36',
            'UF_CHEST_MIN' => '49',
            'UF_CHEST_MAX' => '54',
            'UF_NECK_MIN'  => '32',
            'UF_NECK_MAX'  => '34',
        ],
        [
            'UF_CODE'      => '2XL',
            'UF_BACK_MIN'  => '37',
            'UF_BACK_MAX'  => '41',
            'UF_CHEST_MIN' => '55',
            'UF_CHEST_MAX' => '60',
            'UF_NECK_MIN'  => '35',
            'UF_NECK_MAX'  => '40',
        ],
        [
            'UF_CODE'      => '3XL',
            'UF_BACK_MIN'  => '42',
            'UF_BACK_MAX'  => '50',
            'UF_CHEST_MIN' => '61',
            'UF_CHEST_MAX' => '70',
            'UF_NECK_MIN'  => '41',
            'UF_NECK_MAX'  => '45',
        ],
        [
            'UF_CODE'      => '4XL',
            'UF_BACK_MIN'  => '51',
            'UF_BACK_MAX'  => '55',
            'UF_CHEST_MIN' => '71',
            'UF_CHEST_MAX' => '80',
            'UF_NECK_MIN'  => '46',
            'UF_NECK_MAX'  => '50',
        ],
        [
            'UF_CODE'      => '5XL',
            'UF_BACK_MIN'  => '56',
            'UF_BACK_MAX'  => '65',
            'UF_CHEST_MIN' => '81',
            'UF_CHEST_MAX' => '87',
            'UF_NECK_MIN'  => '51',
            'UF_NECK_MAX'  => '57',
        ],
        [
            'UF_CODE'      => '6XL',
            'UF_BACK_MIN'  => '66',
            'UF_BACK_MAX'  => '79',
            'UF_CHEST_MIN' => '88',
            'UF_CHEST_MAX' => '92',
            'UF_NECK_MIN'  => '58',
            'UF_NECK_MAX'  => '64',
        ],
        [
            'UF_CODE'      => '7XL',
            'UF_BACK_MIN'  => '80',
            'UF_BACK_MAX'  => '89',
            'UF_CHEST_MIN' => '93',
            'UF_CHEST_MAX' => '99',
            'UF_NECK_MIN'  => '65',
            'UF_NECK_MAX'  => '69',
        ],
        [
            'UF_CODE'      => '8XL',
            'UF_BACK_MIN'  => '90',
            'UF_BACK_MAX'  => '',
            'UF_CHEST_MIN' => '100',
            'UF_CHEST_MAX' => '',
            'UF_NECK_MIN'  => '70',
            'UF_NECK_MAX'  => '',
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

        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get(self::HL_BLOCK_SERVICE_NAME);
        foreach ($this->items as $item) {
            $addResult = $dataManager->add($item);
            if ($addResult->isSuccess()) {
                $this->log()->info('Добавлен размер ' . $item['UF_CODE']);
            } else {
                $this->log()->warning(
                    'Не удалось добавить размер: ' . implode(', ', $addResult->getErrorMessages())
                );
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
