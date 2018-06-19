<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Application;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class CatalogDeactivateProductsWithNoOffers20180619024621 extends SprintMigrationBase
{
    protected $description = 'Удаление товаров, не имеющих офферов';

    public function up()
    {
        $productsIblock = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        $offersIblock = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        $propertyId = IblockUtils::getPropertyId($offersIblock, 'CML2_LINK');
        if (!$propertyId) {
            $this->log()->error('Не найдено свойство CML2_LINK');
            return false;
        }
        $data = Application::getConnection()->query(sprintf(
            '
                SELECT e.ID
                FROM b_iblock_element e
                WHERE
                  NOT EXISTS (
                    SELECT * FROM b_iblock_element_prop_s%s p WHERE p.PROPERTY_%s = e.ID
                  )
                  AND e.IBLOCK_ID = %s
            ',
            $offersIblock,
            $propertyId,
            $productsIblock
        ))->fetchAll();

        foreach ($data as $item) {
            $id = $item['ID'];
            if (\CIBlockElement::Delete($id)) {
                $this->log()->info('Удален товар ' . $id);
            } else {
                $this->log()->warning('Не удалось удалить товар ' . $id);
            }
        }
    }

    public function down()
    {
    }
}