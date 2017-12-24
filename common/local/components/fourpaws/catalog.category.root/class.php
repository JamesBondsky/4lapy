<?php

namespace FourPaws\Components;

use Bitrix\Iblock\Component\Tools;
use FourPaws\Catalog\Model\Category;

class CatalogCategoryRoot extends \CBitrixComponent
{
    public function onPrepareComponentParams($params): array
    {
        $params['CATEGORY'] = $params['CATEGORY'] ?? null;
        $params['CATEGORY'] = $params['CATEGORY'] instanceof Category ? $params['CATEGORY'] : null;

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        /**
         * @var Category $category
         */
        $category = $this->arParams['CATEGORY'];


        if ($this->startResultCache()) {
            parent::executeComponent();

            if (!$category || 0 === $category->getChild()->count()) {
                $this->abortResultCache();
                Tools::process404('', true, true, true);
            }

            $this->arResult['CATEGORY'] = $category;
            $this->includeComponentTemplate();
        }

        return $this->arResult['CATEGORY'];
    }
}
