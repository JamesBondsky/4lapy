<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixOrm\Collection\IblockElementCollection;
use FourPaws\Catalog\Model\Offer;

/**
 * Class OfferCollection
 *
 * @package FourPaws\Catalog\Collection
 */
class OfferCollection extends IblockElementCollection
{
    /**
     * @inheritdoc
     */
    protected function fetchElement(): \Generator
    {
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new Offer($fields);
        }
    }
}
