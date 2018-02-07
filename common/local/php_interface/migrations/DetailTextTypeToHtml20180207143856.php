<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Application;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class DetailTextTypeToHtml20180207143856 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    public function __construct()
    {
        $this->description = 'Миграция для установки DETAIL_TEXT_TYPE в HTML для контентных инфоблоков';
        parent::__construct();
    }

    public function up()
    {
        $iblockIds = [
            IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS),
            IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES),
            IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
            IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS),
        ];
        $iblockIds = array_filter($iblockIds);
        if ($iblockIds) {
            Application::getConnection()
                ->query(
                    '
                    UPDATE 
                    b_iblock_element 
                    SET DETAIL_TEXT_TYPE = \'html\' 
                    WHERE IBLOCK_ID IN (' . implode(', ', $iblockIds) . ')'
                );
        }
    }
}
