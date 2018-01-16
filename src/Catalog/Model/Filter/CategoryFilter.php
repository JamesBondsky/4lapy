<?php

namespace FourPaws\Catalog\Model\Filter;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use WebArch\BitrixCache\BitrixCache;

class CategoryFilter extends FilterBase
{
    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Categories';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'sectionIdList';
    }

    /**
     * @inheritdoc
     */
    protected function doGetAllVariants(): VariantCollection
    {
        $doGetAllVariants = function () {
            $variants = [];

            $categoryCollection = (new CategoryQuery())
                ->withFilterParameter('!SECTION_ID', null)
                ->withFilterParameter('DEPTH_LEVEL', 2)
                ->exec();

            /** @var Category $category */
            foreach ($categoryCollection as $category) {
                $variants[] = (new Variant())->withName($category->getName())
                                             ->withValue($category->getId());
            }

            return $variants;
        };

        /** @var Variant[] $variants */
        $variants = (new BitrixCache())->withId(__METHOD__ . $this->getId())
                                       ->withIblockTag(
                                           IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)
                                       )
                                       ->resultOf($doGetAllVariants);

        return new VariantCollection($variants);
    }

}
