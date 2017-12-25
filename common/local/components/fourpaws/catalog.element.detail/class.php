<?php

namespace FourPaws\Components;

use Bitrix\Iblock\Component\Tools;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\ProductQuery;

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
            $this->arResult['PRODUCT'] = $this->getProduct($this->arParams['CODE']);

            if (!$this->arResult['PRODUCT']) {
                $this->abortResultCache();
                Tools::process404([], true, true, true);
            }
            $this->includeComponentTemplate();
        }
        return $this->arResult['PRODUCT'];
    }

    /**
     * @param string $code
     * @return Offer|null
     */
    protected function getProduct(string $code)
    {
        return (new ProductQuery())
            ->withFilter(['CODE' => $code])
            ->exec()
            ->first();
    }
}
