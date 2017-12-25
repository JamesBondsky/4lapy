<?php

namespace FourPaws\Components;

class CatalogProductReviews extends \CBitrixComponent
{
    public function onPrepareComponentParams($params): array
    {
        $params['PRODUCT_ID'] = $params['PRODUCT_ID'] ?: 0;
        $params['PRODUCT_ID'] = (int)$params['PRODUCT_ID'];

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        if (!$this->arParams['PRODUCT_ID']) {
            return;
        }

        if ($this->startResultCache()) {
            parent::executeComponent();
            /**
             * @todo implement logic
             */
            $this->includeComponentTemplate();
        }
    }
}
