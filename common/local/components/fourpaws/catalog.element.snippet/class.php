<?php

namespace FourPaws\Components;

use FourPaws\Catalog\Model\Product;

class CatalogElementSnippet extends \CBitrixComponent
{
    public function onPrepareComponentParams($params)
    {
        $params['PRODUCT'] = $params['PRODUCT'] ?? null;
        $params['PRODUCT'] = $params['PRODUCT'] instanceof Product ? $params['PRODUCT'] : null;
    
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 360000;

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        //if ($this->startResultCache($this->arParams['CACHE_TIME'])) {
            parent::executeComponent();

            if ($this->arParams['PRODUCT']) {
                $this->arResult['PRODUCT'] = $this->arParams['PRODUCT'];

                $this->includeComponentTemplate();
                return;
            }
            //$this->abortResultCache();
        //}
    }
}
