<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Sprint\Migration\Helpers\HlblockHelper;

class HLBlock_cities_field_location20171206125710 extends SprintMigrationBase
{
    protected $description = 'Изменение типа свойства UF_LOCATION у HL-блока городов';

    const HL_BLOCK_NAME = 'Cities';

    const FIELD_NAME = 'UF_LOCATION';

    public function up()
    {
        /** @var HlblockHelper $hlBlockHelper */
        $hlBlockHelper = $this->getHelper()->Hlblock();
        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();

        if (!$hlBlockId = $hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME)) {
            $this->log()->error('HL-блок ' . static::HL_BLOCK_NAME . ' не найден');
        }

        $entityId = 'HLBLOCK_' . $hlBlockId;

        if ($userTypeEntityHelper->deleteUserTypeEntityIfExists($entityId, static::FIELD_NAME)) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' удалено');
        } else {
            $this->log()->warning('Ошибка при удалении пользовательского свойства ' . static::FIELD_NAME);
        }

        if ($userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            static::FIELD_NAME,
            [
                'USER_TYPE_ID'  => 'sale_location',
                'XML_ID'        => static::FIELD_NAME,
                'SORT'          => 500,
                'MULTIPLE'      => 'Y',
                'MANDATORY'     => 'N',
                'SHOW_FILTER'   => 'N',
                'SHOW_IN_LIST'  => 'Y',
                'EDIT_IN_LIST'  => 'Y',
                'IS_SEARCHABLE' => 'N',
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
        }

        $entityId = 'HLBLOCK_' . $hlBlockId;

        if ($userTypeEntityHelper->deleteUserTypeEntityIfExists($entityId, static::FIELD_NAME)) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' удалено');
        } else {
            $this->log()->warning('Ошибка при удалении пользовательского свойства ' . static::FIELD_NAME);
        }

        if ($userTypeEntityHelper->addUserTypeEntityIfNotExists(
            $entityId,
            static::FIELD_NAME,
            [
                'USER_TYPE_ID'  => 'double',
                'XML_ID'        => static::FIELD_NAME,
                'SORT'          => 500,
                'MULTIPLE'      => 'Y',
                'MANDATORY'     => 'N',
                'SHOW_FILTER'   => 'N',
                'SHOW_IN_LIST'  => 'Y',
                'EDIT_IN_LIST'  => 'Y',
                'IS_SEARCHABLE' => 'N',
            ]
        )) {
            $this->log()->info('Пользовательское свойство ' . static::FIELD_NAME . ' создано');
        } else {
            $this->log()->error('Ошибка при создании пользовательского свойства ' . static::FIELD_NAME);

            return false;
        }

        return true;
    }
}
