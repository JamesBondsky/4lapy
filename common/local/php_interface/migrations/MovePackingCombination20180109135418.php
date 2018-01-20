<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class MovePackingCombination20180109135418 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    const PACKING_COMBINATION = 'PACKING_COMBINATION';

    protected $description = 'Перенос свойства объединение по фасовке';

    /**
     * @throws \RuntimeException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Exception
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelper();
        $offerIblockId = $helper->Iblock()->getIblockId(IblockCode::OFFERS, IblockType::CATALOG);
        $productIblockId = $helper->Iblock()->getIblockId(IblockCode::PRODUCTS, IblockType::CATALOG);

        if (!$offerIblockId) {
            throw new \RuntimeException('Cant get offers iblock id');
        }

        if (!$productIblockId) {
            throw new \RuntimeException('Cant get products iblock id');
        }


        $offerProperty = $this->getHelper()->Iblock()->getProperty($offerIblockId, static::PACKING_COMBINATION);
        if (!$offerProperty) {
            throw new \RuntimeException('Property PACKING_COMBINATION not exist in offers iblock');
        }

        $productProperty = $offerProperty;
        unset($productProperty['ID'], $productProperty['IBLOCK_ID']);


        Application::getConnection()->startTransaction();
        try {
            if (!$this->getHelper()->Iblock()->addPropertyIfNotExists($productIblockId, $productProperty)) {
                throw new \RuntimeException('Cant create property in product iblock');
            }

            if (!$this->getHelper()->Iblock()->deletePropertyIfExists($offerIblockId, static::PACKING_COMBINATION)) {
                throw new \RuntimeException('Cant delete property from offer iblock');
            }
        } catch (\Exception $exception) {
            Application::getConnection()->rollbackTransaction();
            throw $exception;
        }

        Application::getConnection()->commitTransaction();
    }

    public function down()
    {

    }
}
