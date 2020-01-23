<?php

namespace FourPaws\Catalog\Model\Filter;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Exception;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Share;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Catalog\Query\ShareQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class ShareFilter
 * @package FourPaws\Catalog\Filter
 */
class ShareFilter extends Abstraction\FilterBase
{
    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Share';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'SHARE';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'share.CODE';
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    protected function doGetAllVariants(): VariantCollection
    {
        $doGetAllVariants = function () {
            $variants = [];

            $shareCollection = (new ShareQuery())->withOrder(['SORT' => 'asc', 'NAME' => 'asc'])
                ->exec();

            /** @var Share $brand */
            foreach ($shareCollection as $share) {
                $variants[] = (new Variant())->withName($share->getName())
                    ->withValue($share->getCode());
            }

            return $variants;
        };

        /** @var Variant[] $variants */
        $variants = (new BitrixCache())->withId(__METHOD__ . $this->getId())
            ->withIblockTag(
                IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::SHARES)
            )
            ->resultOf($doGetAllVariants);

        return new VariantCollection($variants);
    }
}
