<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\Catalog\Model\Offer as OfferModel;

class ProductService
{
    /**
     * @param OfferModel $offer
     * @return array<ShortProduct\Tag()>
     * @throws \Bitrix\Main\SystemException
     */
    public function getTags(OfferModel $offer)
    {
        $tags = [];
        if ($offer->isHit()) {
            $tags[] = (new ShortProduct\Tag())->setImg(MarkHelper::MARK_HIT_IMAGE_SRC);
        }
        if ($offer->isNew()) {
            $tags[] = (new ShortProduct\Tag())->setImg(MarkHelper::MARK_NEW_IMAGE_SRC);
        }
        if ($offer->isSale()) {
            $tags[] = (new ShortProduct\Tag())->setImg(MarkHelper::MARK_SALE_IMAGE_SRC);
        }
        return $tags;
    }

}
