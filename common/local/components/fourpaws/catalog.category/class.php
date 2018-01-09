<?php

namespace FourPaws\Components;

use Bitrix\Iblock\Component\Tools;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use CBitrixComponent;

/** @noinspection AutoloadingIssuesInspection */
class CatalogCategory extends CBitrixComponent
{
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

        $params['SECTION_CODE'] = $params['SECTION_CODE'] ?? '';
        $params['SECTION_CODE'] = (string)$params['SECTION_CODE'];
        $params['SET_TITLE'] = ($params['SET_TITLE'] === 'Y') ? $params['SET_TITLE'] : 'N';

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        if (!$this->arParams['SECTION_CODE']) {
            Tools::process404('', true, true, true);
        }

        if ($this->startResultCache()) {
            parent::executeComponent();

            $this->prepareResult();

            $this->includeComponentTemplate();
        }

        return $this->arResult['CATEGORY'];
    }

    protected function prepareResult()
    {
        global $APPLICATION;

        $category = $this->getCategory($this->arParams['SECTION_CODE']);
        if (!$category) {
            $this->abortResultCache();
            Tools::process404('', true, true, true);
        }

        $title = $category->getName();
        if ($category->getParent() && $category->getParent()->getSuffix()) {
            $title .= ' ' . $category->getParent()->getSuffix();
        }
        $APPLICATION->SetTitle($title);

        $this->arResult['CATEGORY'] = $category;
    }

    /**
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
