<?php

namespace FourPaws\CatalogBundle\Helper;


use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Model\Offer;

/**
 * Class DiscountMarkService
 *
 * @package FourPaws\CatalogBundle\Helper
 */
final class MarkHelper
{
    public const MARK_SALE_IMAGE_SRC = '/static/build/images/inhtml/s-proc.svg';
    public const MARK_GIFT_IMAGE_SRC = '/static/build/images/inhtml/s-gift.svg';
    public const MARK_HIT_IMAGE_SRC = '/static/build/images/inhtml/s-fire.svg';
    public const MARK_NEW_IMAGE_SRC = '/static/build/images/inhtml/new.svg';

    public const MARK_SALE_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-proc.svg" alt="" role="presentation"/>';
    public const MARK_GIFT_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-gift.svg" alt="" role="presentation"/>';
    public const MARK_HIT_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/>';
    public const MARK_NEW_IMAGE = '<img class="b-common-item__sticker" src="/static/build/images/inhtml/new.svg" alt="" role="presentation"/>';

    public const DEFAULT_TRANSPARENT_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:transparent;data-background:transparent;">%s</span>';
    public const DEFAULT_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#da291c;data-background:#da291c;">%s</span>';
    public const YELLOW_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;">%s</span>';
    public const GREEN_TEMPLATE = '<span class="b-common-item__sticker-wrap" style="background-color:#44af2b;data-background:#44af2b;">%s</span>';

    public const GREEN_TEMPLATE_DETAIL_TOP = '<span class="b-common-item__rank-text b-common-item__rank-text--green b-common-item__rank-text--card">%s</span>';


    /**
     * Returns mark`s html
     *
     * @param Offer  $offer
     * @param string $content
     *
     * @param int    $shareId
     *
     * @return string
     */
    public static function getMark(Offer $offer, $content = '', int $shareId = 0): string
    {
        if (!$content) {
            $content = self::getMarkImage($offer, $shareId);
        }

        if ($content) {
            return \sprintf(self::getMarkTemplate($offer, $shareId), $content);
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
     * @param int   $shareId
     *
     * @return string
     */
    private static function getMarkImage(Offer $offer, int $shareId = 0): string
    {
        if ($offer->isShare()) {
            /**
             * @var Share $share
             * @var Share $shareItem
             */
            $share = null;
            if($shareId > 0){
                foreach ($offer->getShare() as $shareItem) {
                    if($shareItem->getId() === $shareId) {
                        $share = $shareItem;
                    }
                }
            }
            if($share === null) {
                $share = $offer->getShare()->first();
            }

            if($share->hasLabelImage()){
                return '<img class="b-common-item__sticker" src="'.$share->getPropertyLabelImageFileSrc().'" alt="" role="presentation"/>';
            }
            if ($share->hasLabel()) {
                return $share->getPropertyLabel();
            }

            return self::MARK_GIFT_IMAGE;
        }

        if ($offer->isSale() || $offer->isSimpleSaleAction() || $offer->isSimpleDiscountAction()) {
            return self::MARK_SALE_IMAGE;
        }

        if ($offer->isHit() || $offer->isPopular()) {
            return self::MARK_HIT_IMAGE;
        }

        if ($offer->isNew()) {
            return self::MARK_NEW_IMAGE;
        }

        return '';
    }

    /**
     * @param Offer $offer
     * @param int $shareId
     *
     * @return string
     */
    private static function getMarkTemplate(Offer $offer, int $shareId = 0): string
    {
        if ($offer->isShare()) {
            /**
             * @var Share $share
             * @var Share $shareItem
             */
            $share = null;
            if ($shareId > 0) {
                foreach ($offer->getShare() as $shareItem) {
                    if ($shareItem->getId() === $shareId) {
                        $share = $shareItem;

                    }
                }
            }
            if ($share === null) {
                $share = $offer->getShare()->first();
            }
            if ($share->hasLabelImage()) {
                return self::DEFAULT_TRANSPARENT_TEMPLATE;
            }
            if ($share->hasLabel()) {
                return self::DEFAULT_TEMPLATE;
            }
        }

        if ($offer->isSale() || $offer->isSimpleSaleAction() || $offer->isSimpleDiscountAction()) {
            /** @todo возможно другой шаблон */
            return self::DEFAULT_TEMPLATE;
        }

        if ($offer->isHit() || $offer->isPopular()) {
            return self::YELLOW_TEMPLATE;
        }

        if ($offer->isNew()) {
            return self::GREEN_TEMPLATE;
        }

        return self::DEFAULT_TEMPLATE;
    }
}
