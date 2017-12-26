<?php

namespace FourPaws\Catalog\Model\Filter;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Catalog\Query\BrandQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class BrandFilter
 * @package FourPaws\Catalog\Filter
 */
class BrandFilter extends Abstraction\FilterBase
{
    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Brand';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'BRAND';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'brand.CODE';
    }

    /**
     * @inheritdoc
     */
    protected function doGetAllVariants(): VariantCollection
    {
        $doGetAllVariants = function () {
            $variants = [];

            $brandCollection = (new BrandQuery())->withOrder([])
                ->exec();

            /** @var Brand $brand */
            foreach ($brandCollection as $brand) {
                $variants[] = (new Variant())->withName($brand->getName())
                    ->withValue($brand->getCode());
            }

            return $variants;
        };

        /** @var Variant[] $variants */
        $variants = (new BitrixCache())->withId(__METHOD__ . $this->getId())
            ->withIblockTag(
                IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS)
            )
            ->resultOf($doGetAllVariants);

        return new VariantCollection($variants);
    }
}
