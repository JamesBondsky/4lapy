<?php

namespace FourPaws\Components;

use Bitrix\Iblock\Component\Tools;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;

class CatalogCategoriesList extends \CBitrixComponent
{
    public function onPrepareComponentParams($params): array
    {
        $params['ID'] = $params['ID'] ?? 0;
        $params['ID'] = (int)$params['ID'];

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        if (!$this->arParams['ID']) {
            Tools::process404('', true, true, true);
        }

        if ($this->startResultCache()) {
            parent::executeComponent();
            $category = $this->getCategory($this->arParams['ID']);
            if (!$category || 0 === $category->getChild()->count()) {
                $this->abortResultCache();
                Tools::process404('', true, true, true);
            }

            $this->arResult['CATEGORY'] = $category;
            $this->includeComponentTemplate();
        }

        return $this->arResult['CATEGORY'];
    }

    /**
     *
     * @param int $id
     *
     * @return null|Category
     */
    protected function getCategory(int $id)
    {
        return (new CategoryQuery())
            ->withFilterParameter('CNT_ACTIVE', 'Y')
            ->withFilterParameter('ID', $id)
            ->withCountElements(true)
            ->withNav(['nTopCount' => 1])
            ->exec()
            ->first();
    }
}
