<?php

namespace FourPaws\CatalogBundle\Service;


use FourPaws\Catalog\Model\Offer;

/**
 * Class DiscountMarkService
 *
 * @package FourPaws\CatalogBundle\Service
 */
final class DiscountMarkService
{
    const MARK_DISCOUNT_PATH = '/static/build/images/inhtml/s-15proc.svg';
    const MARK_SALE_PATH = '/static/build/images/inhtml/s-proc.svg';
    const MARK_GIFT_PATH = '/static/build/images/inhtml/s-gift.svg';

    public function getMarkImagePath(Offer $offer): string
    {
        if ($offer->getDiscount()) {
            return self::MARK_DISCOUNT_PATH;
        }

        if ($offer->isSimpleSaleAction()) {
            return self::MARK_SALE_PATH;
        }

        if ($offer->hasAction()) {
            return self::MARK_GIFT_PATH;
        }

        return '';
    }
}
