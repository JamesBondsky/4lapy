<?php

namespace FourPaws\Catalog\Model\Filter\Abstraction;

use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Variant;

abstract class CheckboxFilter extends FilterBase
{
    public function doGetAllVariants(): VariantCollection
    {
        // В коллекции только true, чтобы при отключении чекбокса отображать
        // все возможные варианты, а не только те, у которых значение false
        return new VariantCollection(
            [
                (new Variant())->withValue('1')->withName('Checked'),
            ]
        );
    }
}
