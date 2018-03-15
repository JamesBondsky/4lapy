<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AccessFoodSelection20180315103606 extends SprintMigrationBase
{

    protected const TECH_USER_GROUP = 8;
    protected $description = 'добавление доступа техническим пользователям в подбор корма';

    public function up()
    {
        try {
            $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::FOOD_SELECTION);
            $permissions = \CIBlock::GetGroupPermissions($iblockId);
            if (!array_key_exists(static::TECH_USER_GROUP, $permissions) || (array_key_exists(static::TECH_USER_GROUP,
                        $permissions) && $permissions[static::TECH_USER_GROUP] !== 'W')) {
                $permissions[static::TECH_USER_GROUP] = 'W';
                \CIBlock::SetPermission($iblockId, $permissions);
            } else {
                $this->log()->info('Доступ уже установлен');
            }
        } catch (IblockNotFoundException $e) {
            $this->log()->warning('инфоблок не найден');
        }

    }
}
