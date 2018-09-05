<?php

namespace FourPaws\Catalog\Model\Filter\Abstraction;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Query\D7QueryBase;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Variant;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class ReferenceFilterBase
 *
 * @package FourPaws\Catalog\Model\Filter\Abstraction
 */
abstract class ReferenceFilterBase extends FilterBase
{
    /**
     * @return array
     */
    protected function getVariantOrder(): array {
        return [
            'UF_SORT' => 'asc',
            'UF_NAME' => 'asc',
        ];
    }

    /**
     * @return string
     */
    abstract protected function getHlBlockServiceName(): string;

    /**
     * @throws Exception
     * @return VariantCollection
     */
    protected function doGetAllVariants(): VariantCollection
    {
        $doGetAllVariants = function () {

            $variants = [];

            /** @var HlbReferenceItemCollection $referenceItemCollection */
            $referenceItemCollection = $this->getReferenceQuery()
                ->withOrder($this->getVariantOrder())
                ->exec();

            /** @var HlbReferenceItem $referenceItem */
            foreach ($referenceItemCollection as $referenceItem) {
                $variants[] = $this->getVariant($referenceItem);
            }

            return $variants;
        };

        $variants = (new BitrixCache())->withId(__METHOD__ . $this->getFilterCode())
                                       ->withTag('catalog:referenceFilters')
                                       ->resultOf($doGetAllVariants);

        return new VariantCollection($variants);
    }

    /**
     * @return D7QueryBase
     * @throws ArgumentException
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    protected function getReferenceQuery(): D7QueryBase
    {
        $dataManager = Application::getHlBlockDataManager($this->getHlBlockServiceName());

        return (new HlbReferenceQuery($dataManager::query()))->withFilter($this->getDefaultFilter());
    }

    /**
     * @return array
     */
    protected function getDefaultFilter(): array
    {
        return [
            'UF_HIDE_IN_FILTER' => false
        ];
    }

    /**
     * @param HlbReferenceItem $referenceItem
     *
     * @return Variant
     */
    protected function getVariant(HlbReferenceItem $referenceItem): Variant
    {
        return (new Variant())
            ->withName($referenceItem->getName())
            ->withValue($referenceItem->getXmlId())
            ->withImage($referenceItem->getFile());
    }
}
