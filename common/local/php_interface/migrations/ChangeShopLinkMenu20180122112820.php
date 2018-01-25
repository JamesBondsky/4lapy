<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Collection\IblockElementCollection;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class ChangeShopLinkMenu20180122112820 extends SprintMigrationBase
{
    
    protected $description = "Изменение ссылки меню магазины";
    
    public function up()
    {
        $obEl       = new IblockElementQuery;
        $obElBitrix = new \CIBlockElement();
        /** @var IblockElementCollection $res */
        try {
            $res = $obEl->withSelect(['ID'])->withFilter(
                [
                    '=NAME'        => 'Магазины',
                    '=IBLOCK_ID'   => IblockUtils::getIblockId(
                        IblockType::MENU,
                        IblockCode::MAIN_MENU
                    ),
                    '=DEPTH_LEVEL' => 1,
                ]
            )->exec();
            if (!$res->isEmpty()) {
                /** @var IblockElement $item */
                $item = $res->current();
                $obElBitrix->Update(
                    $item->getId(),
                    [
                        'PROPERTY_VALUES' => ['HREF' => '/company/shops/'],
                        'CODE'          => 'company_shops',
                    ]
                );
            }
        } catch (IblockNotFoundException $e) {
        }
    }
    
    public function down()
    {
    }
    
}
