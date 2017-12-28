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
        $params['CODE'] = $params['CODE'] ?? '';

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
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

            $offers = $product->getOffers();
            $packingCombinations = [];
            /** @var Offer $offer */
            foreach ($offers as $offer) {
                if ($offer->getClothingSize() || $offer->getVolumeReference()) {
                    $packingCombinations[$offer->getPackingCombination()] = $offer;
                }
                /*
                if ($offer->getColor() && $offer->getColourCombination()) {
                    $colors[] = $offer->getColor();
                    $combinations['COLOR'][$offer->getColourCombination()] = $offer;
                }
                */
                if ($offer->getFlavourCombination()) {
                    /* @todo выбрать связанные товары? */
                }
            }

            $this->arResult = [
                'PRODUCT'      => $product,
                'PACKING_COMBINATIONS' => $packingCombinations,
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
