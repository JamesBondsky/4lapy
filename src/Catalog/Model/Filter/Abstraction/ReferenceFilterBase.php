<?php

namespace FourPaws\Catalog\Model\Filter\Abstraction;

use Exception;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Variant;
use WebArch\BitrixCache\BitrixCache;

abstract class ReferenceFilterBase extends FilterBase
{
    protected function getVariantOrder(): array {
        return [
            'UF_SORT' => 'asc',
            'UF_NAME' => 'asc',
        ];
    }

    abstract protected function getHlBlockServiceName(): string;

    /**
     * @throws Exception
     * @return VariantCollection
     */
    protected function doGetAllVariants(): VariantCollection
    {
        $doGetAllVariants = function () {
            $dataManager = Application::getHlBlockDataManager($this->getHlBlockServiceName());

            $variants = [];

            /** @var HlbReferenceItemCollection $referenceItemCollection */
            $referenceItemCollection = (new HlbReferenceQuery($dataManager::query()))
                ->withFilter([])
                ->withOrder($this->getVariantOrder())
                ->exec();

            /** @var HlbReferenceItem $referenceItem */
            foreach ($referenceItemCollection as $referenceItem) {
                $variants[] = (new Variant())->withName($referenceItem->getName())
                                             ->withValue($referenceItem->getXmlId());
            }

            return $variants;
        };

        $variants = (new BitrixCache())->withId(__METHOD__ . $this->getFilterCode())
                                       ->withTag('catalog:referenceFilters')
                                       ->resultOf($doGetAllVariants);

        return new VariantCollection($variants);
    }
}
