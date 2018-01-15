<?php

namespace FourPaws\Components;

use Bitrix\Catalog\CatalogViewedProductTable;
use Bitrix\Catalog\Product\Basket;
use Bitrix\Iblock\Component\Tools;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\ProductQuery;

/** @noinspection AutoloadingIssuesInspection */
class CatalogElementDetailComponent extends \CBitrixComponent
{
    const EXPAND_CLOSURES = 'EXPAND_CLOSURES';

    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }
        $params['CODE'] = $params['CODE'] ?? '';
        $params['SET_TITLE'] = ($params['SET_TITLE'] === 'Y') ? $params['SET_TITLE'] : 'N';

        $params['SET_VIEWED_IN_COMPONENT'] = isset($params['SET_VIEWED_IN_COMPONENT']) ? $params['SET_VIEWED_IN_COMPONENT'] : 'Y';

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        global $APPLICATION;

        if (!$this->arParams['CODE']) {
            Tools::process404([], true, true, true);
        }

        if ($this->startResultCache()) {
            parent::executeComponent();

            /** @var Product $product */
            $product = $this->getProduct($this->arParams['CODE']);

            if (!$product) {
                $this->abortResultCache();
                Tools::process404([], true, true, true);
            }

            if ($this->arParams['SET_TITLE'] === 'Y') {
                $APPLICATION->SetTitle($product->getName());
            }

            $this->arResult = [
                'PRODUCT' => $product,
            ];

            $this->includeComponentTemplate();
        }

        $this->saveViewedProduct();

        return $this->arResult['PRODUCT'];
    }

    /**
     * @param string $code
     *
     * @return null|Offer
     */
    protected function getProduct(string $code)
    {
        return (new ProductQuery())
            ->withFilterParameter('CODE', $code)
            ->exec()
            ->first();
    }

    /**
     * Добавление в просмотренные товары при генерации результата
     */
    protected function saveViewedProduct() {
        if ($this->arParams['SET_VIEWED_IN_COMPONENT'] === 'Y' && !empty($this->arResult['PRODUCT'])) {
            // задано действие добавления в просмотренные при генерации результата
            // (в идеале это нужно делать черех ajax)
            if (Basket::isNotCrawler()) {
                /** @var Product $product */
                $product = $this->arResult['PRODUCT'];
                $currentOffer = $product->getOffers()->first();
                $parentId = $product->getId();
                $productId = $currentOffer ? $currentOffer->getId() : 0;
                $productId = $productId > 0 ? $productId : $parentId;

                // check if there was a recommendation
                $recommendationId = '';
                /*
                $recommendationCookie = $GLOBALS['APPLICATION']->get_cookie(\Bitrix\Main\Analytics\Catalog::getCookieLogName());
                if (!empty($recommendationCookie)) {
                    $recommendations = \Bitrix\Main\Analytics\Catalog::decodeProductLog($recommendationCookie);
                    if (is_array($recommendations) && isset($recommendations[$parentID])) {
                        $recommendationId = $recommendations[$parentID][0];
                    }
                }
                */

                CatalogViewedProductTable::refresh(
                    $productId,
                    \CSaleBasket::GetBasketUserID(),
                    $this->getSiteId(),
                    $parentId,
                    $recommendationId
                );
            }
        }
    }
}
