<?php

namespace FourPaws\Components;

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
}
