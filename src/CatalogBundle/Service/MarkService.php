<?php

namespace FourPaws\CatalogBundle\Service;


use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Model\Offer;

/**
 * Class DiscountMarkService
 *
 * @package FourPaws\CatalogBundle\Service
 */
final class MarkService
{
    public const MARK_SALE_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-proc.svg" alt="" role="presentation"/>';
    public const MARK_GIFT_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-gift.svg" alt="" role="presentation"/>';
    public const MARK_HIT_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/>';
    public const MARK_NEW_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/new.svg" alt="" role="presentation"/>';

    public const DEFAULT_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#da291c;data-background:#da291c;">%s</span>';
    public const YELLOW_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;">%s</span>';
    public const GREEN_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#44af2b;data-background:#44af2b;">%s</span>';

    public const GREEN_TEMPLATE_DETAIL_TOP = '<<span class="b-common-item__rank-text b-common-item__rank-text--green b-common-item__rank-text--card">%s</span>';


    /**
     * Returns mark`s html
     *
     * @param Offer  $offer
     * @param string $content
     *
     * @return string
     */
    public static function getMark(Offer $offer, $content = ''): string
    {
        /**
         * @todo get content from promo actions
         */

        if (!$content) {
            $content = self::getMarkImage($offer);
        }

        if ($content) {
            return \sprintf(self::getMarkTemplate($offer), $content);
        }

        return '';
    }

    /**
     * Returns mark`s html
     *
     * @param Offer $offer
     *
     * @return string
     */
    public static function getDetailTopMarks(Offer $offer): string
    {
        $html = '';
        if ($offer->isNew()) {
            $html .= self::getDetailTopMark(self::GREEN_TEMPLATE_DETAIL_TOP, 'Новинка');
        }

        if ($offer->isHit()) {
            $html .= self::getDetailTopMark(self::GREEN_TEMPLATE_DETAIL_TOP, 'Хит');
        }

        if ($offer->isPopular()) {
            $html .= self::getDetailTopMark(self::GREEN_TEMPLATE_DETAIL_TOP, 'Популярный');
        }

        if ($offer->isSale()) {
            $html .= self::getDetailTopMark(self::GREEN_TEMPLATE_DETAIL_TOP, 'Распродажа');
        }

        return $html;
    }

    /**
     * @param $template
     * @param $content
     *
     * @return string
     */
    private static function getDetailTopMark($template, $content): string
    {
        return \sprintf($template, $content);
    }

    /**
     * @param Offer $offer
     *
     * @return string
     */
    private static function getMarkImage(Offer $offer): string
    {
        if ($offer->isHit()) {
            return self::MARK_HIT_IMAGE;
        }

        if ($offer->isPopular()) {
            /** @todo возможно другой шаблон */
            return self::MARK_HIT_IMAGE;
        }

        if ($offer->isNew()) {
            return self::MARK_NEW_IMAGE;
        }

        if ($offer->isSale()) {
            /** @todo возможно другой шаблон */
            return self::MARK_SALE_IMAGE;
        }

        if ($offer->isSimpleSaleAction()) {
            return self::MARK_SALE_IMAGE;
        }

        if ($offer->isShare()) {
            /** @var Share $share */
            $share = $offer->getShare()->first();
            if (!empty($share->getPropertyLabel())) {
                return $share->getPropertyLabel();
            }

            return self::MARK_GIFT_IMAGE;
        }

        return '';
    }

    /**
     * @param Offer $offer
     *
     * @return string
     */
    private static function getMarkTemplate(Offer $offer): string
    {
        if ($offer->isHit()) {
            return self::YELLOW_TEMPLATE;
        }

        if ($offer->isNew()) {
            return self::GREEN_TEMPLATE;
        }

        if ($offer->isSale()) {
            /** @todo возможно другой шаблон */
            return self::DEFAULT_TEMPLATE;
        }

        if ($offer->isPopular()) {
            /** @todo возможно другой шаблон */
            return self::YELLOW_TEMPLATE;
        }

        return self::DEFAULT_TEMPLATE;
    }
}
