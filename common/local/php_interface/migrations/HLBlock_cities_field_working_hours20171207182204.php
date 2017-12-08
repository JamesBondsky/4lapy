<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Sprint\Migration\Helpers\HlblockHelper;

class HLBlock_cities_field_working_hours20171207182204 extends SprintMigrationBase
{
    protected $description = 'Добавление свойства UF_WORKING_HOURS в HL-блок городов';

    const HL_BLOCK_NAME = 'Cities';

    const FIELD_NAME = 'UF_WORKING_HOURS';

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

        if ($userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            static::FIELD_NAME,
            [
                'USER_TYPE_ID'  => 'string',
                'XML_ID'        => static::FIELD_NAME,
                'SORT'          => 500,
                'MULTIPLE'      => 'N',
                'MANDATORY'     => 'N',
                'SHOW_FILTER'   => 'N',
                'SHOW_IN_LIST'  => 'Y',
                'EDIT_IN_LIST'  => 'Y',
                'IS_SEARCHABLE' => 'N',
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'Время работы',
                    'en' => 'Working hours',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'Время работы',
                    'en' => 'Working hours',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'Время работы',
                    'en' => 'Working hours',
                ],
            ]
        )) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' создано');
        } else {
            $this->log()->error('Ошибка при создании пользовательского свойства ' . static::FIELD_NAME);

            return false;
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

        if ($userTypeEntityHelper->deleteUserTypeEntityIfExists($entityId, static::FIELD_NAME)) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' удалено');
        } else {
            $this->log()->error('Ошибка при удалении пользовательского свойства ' . static::FIELD_NAME);
            return false;
        }

        return true;
    }
}
