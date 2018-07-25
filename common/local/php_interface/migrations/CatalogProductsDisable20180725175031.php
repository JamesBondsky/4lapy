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

class CatalogProductsDisable20180725175031 extends SprintMigrationBase
{
    /**
     * @var string
     */
    protected $description = 'Удаление товаров, не имеющих офферов';

    public function up()
    {
        $iblockHelper = $this->getHelper()->Iblock();

        $productsIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        $offersIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        $cml2propertyId = $iblockHelper->getPropertyId($offersIblockId, 'CML2_LINK');
        if (!$cml2propertyId) {
            $this->log()->error('Не найдено свойство привязки к товару у ИБ офферов');
            return false;
        }

        $products = Application::getConnection()->query(
            sprintf(
                'SELECT e.ID as ID
                  FROM b_iblock_element e
                  WHERE
                    e.IBLOCK_ID = %s AND
                    NOT EXISTS (SELECT * FROM b_iblock_element_prop_s%s p WHERE p.PROPERTY_%s = e.ID)
                ',
                $productsIblockId,
                $offersIblockId,
                $cml2propertyId
            )
        )->fetchAll();

        foreach ($products as $product) {
            \CIBlockElement::Delete($product['ID']);
        }

        return true;
    }

    public function down()
    {

    }
}