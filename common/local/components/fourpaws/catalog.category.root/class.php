<?php

namespace FourPaws\Components;

use Bitrix\Iblock\Component\Tools;
use FourPaws\Catalog\Model\Category;
use CBitrixComponent;

CBitrixComponent::includeComponentClass('fourpaws:catalog.category');

/** @noinspection AutoloadingIssuesInspection */
class CatalogCategoryRoot extends CatalogCategory
{

    protected function prepareResult()
    {
        global $APPLICATION;

        parent::prepareResult();

        /** @var Category $category */
        $category = $this->arResult['CATEGORY'];
        if ($this->arParams['SET_TITLE'] === 'Y') {
            $APPLICATION->SetTitle($category->getDisplayName() ?: $category->getName());
        }

        if (!$category->getChild()->count()) {
            $this->abortResultCache();
            Tools::process404('', true, true, true);
        }
    }

}
