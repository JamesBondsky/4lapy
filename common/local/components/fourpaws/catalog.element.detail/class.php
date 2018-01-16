<?php

namespace FourPaws\Components;

use Bitrix\Catalog\CatalogViewedProductTable;
use Bitrix\Catalog\Product\Basket;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\Analytics\Catalog;
use Bitrix\Main\Analytics\Counter;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Text\JsExpression;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Catalog\Query\CategoryQuery;

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

            $sectionId = intval(reset($product->getSectionsIdList()));

            $this->arResult = [
                'PRODUCT' => $product,
                'SECTION_CHAIN' => $this->getSectionChain($sectionId),
                // возможно, понадобится в будущем
                //'SECTION' => $this->getSection($sectionId),
            ];

            $this->includeComponentTemplate();
        }

        // bigdata
        $this->obtainCounterData();
        $this->sendCounters();

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
     * @param int $sectionId
     *
     * @return array
     */
    protected function getSectionChain(int $sectionId)
    {
        $sectionChain = [];
        if ($sectionId > 0) {
            $items = \CIBlockSection::GetNavChain(false, $sectionId, ['ID', 'NAME']);
            while ($item = $items->getNext(true, false)) {
                $sectionChain[] = $item;
            }
        }

        return $sectionChain;
    }

    /**
     * @param int $sectionId
     *
     * @return null|Category
     */
    protected function getSection(int $sectionId)
    {
        if ($sectionId <= 0) {
            return null;
        }

        return (new CategoryQuery())
            ->withFilterParameter('ID', $sectionId)
            ->exec()
            ->first();
    }

    /**
     * Добавление в просмотренные товары при генерации результата
     */
    protected function saveViewedProduct()
    {
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
                $recommendationCookie = $GLOBALS['APPLICATION']->get_cookie(Catalog::getCookieLogName());
                if (!empty($recommendationCookie)) {
                    $recommendations = Catalog::decodeProductLog($recommendationCookie);
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

    /**
     * Получение данных для BigData
     *
     * @return void
     */
    protected function obtainCounterData()
    {
        if (empty($this->arResult['PRODUCT'])) {
            return;
        }
        /** @var Product $product */
        $product = $this->arResult['PRODUCT'];

        $categoryId = '';
        $categoryPath = [];
        if ($this->arResult['SECTION_CHAIN']) {
            foreach ($this->arResult['SECTION_CHAIN'] as $cat)  {
                $categoryPath[$cat['ID']] = $cat['NAME'];
                $categoryId = $cat['ID'];
            }
        }

        $counterData = array(
            'product_id' => $product->getId(),
            'iblock_id' => $product->getIblockId(),
            'product_title' => $product->getName(),
            'category_id' => $categoryId,
            'category' => $categoryPath
        );

        $offers = $product->getOffers();
        $currentOffer = $offers ? $offers->first() : null;
        $counterData['price'] = $currentOffer ? $currentOffer->getPrice() : 0;
        $counterData['currency'] = $currentOffer ? $currentOffer->getCurrency() : '';

        // make sure it is in utf8
        $counterData = Encoding::convertEncoding($counterData, SITE_CHARSET, 'UTF-8');

        // pack value and protocol version
        $rcmLogCookieName = Option::get('main', 'cookie_name', 'BITRIX_SM').'_'.\Bitrix\Main\Analytics\Catalog::getCookieLogName();

        $this->arResult['counterDataSource'] = $counterData;
        $this->arResult['counterData'] = [
            'item' => base64_encode(json_encode($counterData)),
            'user_id' => new JsExpression(
                'function(){return BX.message("USER_ID") ? BX.message("USER_ID") : 0;}'
            ),
            'recommendation' => new JsExpression(
                'function() {
                    var rcmId = "";
                    var cookieValue = BX.getCookie("' . $rcmLogCookieName . '");
                    var productId = ' . $product->getId() . ';
                    var cItems = [];
                    var cItem;

                    if (cookieValue)
                    {
                        cItems = cookieValue.split(".");
                    }

                    var i = cItems.length;
                    while (i--)
                    {
                        cItem = cItems[i].split("-");
                        if (cItem[0] == productId)
                        {
                            rcmId = cItem[1];
                            break;
                        }
                    }

                    return rcmId;
                }'
            ),
            'v' => '2'
        ];
    }

    /**
     * Отправка bigdata
     *
     * @return void
     */
    protected function sendCounters()
    {
        if (isset($this->arResult['counterData']) && Catalog::isOn())  {
            Counter::sendData('ct', $this->arResult['counterData']);
        }
    }
}
