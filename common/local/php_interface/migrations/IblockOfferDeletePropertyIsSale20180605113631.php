<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockOfferDeletePropertyIsSale20180605113631 extends SprintMigrationBase
{
    private const PROPERTY_CODE = 'IS_SALE';

    public function up()
    {
        /** @var \Sprint\Migration\Helpers\IblockHelper $iblockHelper */
        $iblockHelper = $this->getHelper()->Iblock();
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        if ($iblockHelper->getProperty($iblockId, self::PROPERTY_CODE)) {
            if ($iblockHelper->deletePropertyIfExists($iblockId, self::PROPERTY_CODE)) {
                $this->log()->info('Удалено свойство ' . self::PROPERTY_CODE);
            } else {
                $this->log()->error('Ошибка при удалении свойства ' . self::PROPERTY_CODE);
                return false;
            }
        }

        return true;
    }

    public function down()
    {

    }
}