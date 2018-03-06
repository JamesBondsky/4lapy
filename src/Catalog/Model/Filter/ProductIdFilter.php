<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\Catalog\Model\Filter;


use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Variant;

class ProductIdFilter extends FilterBase
{

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'products';
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
        return 'ID';
    }

    /**
     * @inheritdoc
     */
    public function doGetAllVariants(): VariantCollection
    {
        return new VariantCollection();
    }

    /**
     * @param array $checkedValues
     */
    public function setCheckedVariants(array $checkedValues)
    {
        $allVariants = $this->getAllVariants();
        foreach ($checkedValues as $value) {
            $allVariants->add((new Variant())->withAvailable(true)
                                             ->withName($value)
                                             ->withValue($value)
                                             ->withChecked(true));
        }
    }
}