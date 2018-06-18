<?php

namespace FourPaws\Components;

use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use CBitrixComponent;
use FourPaws\App\Templates\MediaEnum;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Helpers\TaggedCacheHelper;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection
 *
 * Class CatalogElementSnippet
 */
class CatalogElementSnippet extends CBitrixComponent
{
    /**
     * @param array $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['PRODUCT'] = $params['PRODUCT'] ?? null;
        $params['PRODUCT'] = $params['PRODUCT'] instanceof Product ? $params['PRODUCT'] : null;

        $params['NOT_CATALOG_ITEM_CLASS'] = $params['NOT_CATALOG_ITEM_CLASS'] ?? 'N';

        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 360000;
        $params['CACHE_TYPE'] = $params['CACHE_TIME'] === 0 ? 'N' : $params['CACHE_TYPE'];

        $params['OFFER_FILTER'] = $params['OFFER_FILTER'] ?? [];
        $params['SHARE_ID'] = $params['SHARE_ID'] ?? 0;
        $params['SHARE_ID'] = (int)$params['SHARE_ID'];

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @return void
     *
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws SystemException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            parent::executeComponent();

            if ($this->arParams['PRODUCT']) {
                /** @var Product $product */
                $this->arResult['PRODUCT'] = $product = $this->arParams['PRODUCT'];

                $this->arResult['CURRENT_OFFER'] = $currentOffer = $this->getCurrentOffer($product);

                TaggedCacheHelper::addManagedCacheTags([
                    'catalog:offer:' . $currentOffer->getId(),
                    'catalog:product:' . $product->getId(),
                    'iblock:item:' . $currentOffer->getId(),
                    'iblock:item:' . $product->getId(),
                ]);

                $this->includeComponentTemplate();

                return;
            }

            $this->abortResultCache();
        }
    }

    /**
     * @param Product $product
     *
     * @return mixed|null
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    protected function getCurrentOffer(Product $product)
    {
        if (!empty($this->arParams['CURRENT_OFFER']) && $this->arParams['CURRENT_OFFER'] instanceof Offer) {
            $currentOffer = $this->arParams['CURRENT_OFFER'];
        } else {
            $offers = $product->getOffers(true, false, $this->arParams['OFFER_FILTER']);
            $currentOffer = null;
            foreach ($offers as $offer) {
                if ($offer->getImages()->count() >= 1 && $offer->getImages()->first() !== MediaEnum::NO_IMAGE_WEB_PATH) {
                    $currentOffer = $offer;
                }
            }

            if (!($currentOffer instanceof Offer)) {
                $currentOffer = $offers->first();
            }
        }

        return $currentOffer;
    }
}
