<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Application;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class CatalogAutosorter20180608141643 extends SprintMigrationBase
{

    protected $description = 'Простановка всем товарам свойства "APPLY_AUTOSORT"';

    private const PROP_CODE = 'APPLY_AUTOSORT';

    public function up()
    {
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        if (!$iblockId) {
            $this->log()->error('Не найден ИБ товаров');
            return false;
        }

        if (!$propId = IblockUtils::getPropertyId($iblockId, self::PROP_CODE)) {
            $this->log()->error('Не найдено свойство ' . self::PROP_CODE);
            return false;
        }

        Application::getConnection()->query(
            sprintf('UPDATE b_iblock_element_prop_s%s SET PROPERTY_%s = 1', $iblockId, $propId)
        );

        return true;
    }

    public function down()
    {

    }
}
