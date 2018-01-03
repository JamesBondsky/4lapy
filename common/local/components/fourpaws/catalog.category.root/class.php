<?php

namespace FourPaws\Components;

use Bitrix\Iblock\Component\Tools;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;

/** @noinspection AutoloadingIssuesInspection */
class CatalogCategoryRoot extends \CBitrixComponent
{
    public function onPrepareComponentParams($params): array
    {
        $params['SECTION_CODE'] = $params['SECTION_CODE'] ?? '';
        $params['SECTION_CODE'] = (string)$params['SECTION_CODE'];

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        if (!$this->arParams['SECTION_CODE']) {
            Tools::process404('', true, true, true);
        }

        if ($this->startResultCache()) {
            parent::executeComponent();
            $category = $this->getCategory($this->arParams['SECTION_CODE']);
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
     * @param string $slug
     *
     * @return null|Category
     */
    protected function getCategory(string $slug)
    {
        return (new CategoryQuery())
            ->withFilterParameter('CNT_ACTIVE', 'Y')
            ->withFilterParameter('CODE', $slug)
            ->withCountElements(true)
            ->withNav(['nTopCount' => 1])
            ->exec()
            ->first();
    }
}
