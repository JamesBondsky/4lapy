<?php

namespace FourPaws\Catalog\Model\Filter;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\ColorReferenceItem;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Query\D7QueryBase;
use FourPaws\BitrixOrm\Query\HlbColorQuery;
use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterNested;
use FourPaws\Catalog\Model\Variant;

/**
 * Class ColourFilter
 *
 * @package FourPaws\Catalog\Model\Filter
 */
class ColourFilter extends ReferenceFilterNested
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.colour';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Colour';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'COLOUR';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return 'offers';
    }

    /**
     * @return string
     */
    public function getNestedRuleCode(): string
    {
        return 'PROPERTY_COLOUR';
    }

    /**
     * @param HlbReferenceItem $referenceItem
     * @return Variant
     */
    protected function getVariant(HlbReferenceItem $referenceItem): Variant
    {
        $result = (new Variant())->withName($referenceItem->getName())
            ->withValue($referenceItem->getXmlId())
            ->withImage($referenceItem->getFile());

        if ($referenceItem instanceof  ColorReferenceItem) {
            $result->withColor($referenceItem->getColorCode());
        }

        return $result;
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

        return (new HlbColorQuery($dataManager::query()))->withFilter([]);
    }
}
