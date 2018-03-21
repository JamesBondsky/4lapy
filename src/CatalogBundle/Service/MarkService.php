<?php

namespace FourPaws\CatalogBundle\Service;


use FourPaws\Catalog\Model\Offer;

/**
 * Class DiscountMarkService
 *
 * @package FourPaws\CatalogBundle\Service
 */
final class MarkService
{
    const MARK_SALE_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-proc.svg" alt="" role="presentation"/>';
    const MARK_GIFT_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-gift.svg" alt="" role="presentation"/>';
    const MARK_HIT_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/>';
    const MARK_NEW_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/new.svg" alt="" role="presentation"/>';

    const DEFAULT_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#da291c;data-background:#da291c;">%s</span>';
    const YELLOW_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;">%s</span>';
    const GREEN_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#44af2b;data-background:#44af2b;">%s</span>';


    /**
     * Returns mark`s html
     *
     * @param Offer  $offer
     * @param string $content
     *
     * @return string
     */
    public function getMark(Offer $offer, $content = ''): string
    {
        /**
         * @todo get content from promo actions
         */
        
        if (!$content) {
            $content = $this->getMarkImage($offer);
        }
        
        if ($content) {
            return \sprintf($this->getMarkTemplate($offer), $content);
        }
        
        return '';
    }

    /**
     * @param Offer $offer
     *
     * @return string
     */
    private function getMarkImage(Offer $offer): string
    {
        if ($offer->isHit()) {
            return self::MARK_HIT_IMAGE;
        }

        if ($offer->isNew()) {
            return self::MARK_NEW_IMAGE;
        }

        if ($offer->isSimpleSaleAction()) {
            return self::MARK_SALE_IMAGE;
        }

        if ($offer->hasAction()) {
            return self::MARK_GIFT_IMAGE;
        }

        return '';
    }

    /**
     * @param Offer $offer
     *
     * @return string
     */
    private function getMarkTemplate(Offer $offer): string
    {
        if ($offer->isNew()) {
            return self::GREEN_TEMPLATE;
        }

        if ($offer->isHit()) {
            return self::YELLOW_TEMPLATE;
        }

        return self::DEFAULT_TEMPLATE;
    }
}
