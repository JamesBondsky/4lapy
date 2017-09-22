<?php

namespace FourPaws\Catalog\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixIblockORM\Query\IblockElementQuery;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class ProductQuery extends IblockElementQuery
{
    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        return [
            'IBLOCK_ID',
            'ID',
            'NAME',
            'XML_ID',
            'CODE',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getBaseFilter(): array
    {
        return ['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)];
    }

    /**
     * @return ProductCollection
     */
    public function exec(): ProductCollection
    {
        return new ProductCollection($this->doExec());
    }

}
