<?php

namespace FourPaws\Components;

use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\InheritedProperty\SectionValues;
use CBitrixComponent;
use Exception;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use WebArch\BitrixCache\BitrixCache;

/** @noinspection AutoloadingIssuesInspection */
class CatalogCategory extends CBitrixComponent
{
    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000;
        }

        $params['SECTION_CODE'] = $params['SECTION_CODE'] ?? '';
        $params['SET_TITLE'] = ($params['SET_TITLE'] === 'Y') ? $params['SET_TITLE'] : 'N';
        $params['PRODUCT_COUNT'] = $params['PRODUCT_COUNT'] ?: '0 товаров';
        $params['MIN_PRICE_PRODUCT'] = $params['MIN_PRICE_PRODUCT'] ?? false;

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @return Category
     */
    public function executeComponent(): ?Category
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

    /**
     *
     */
    protected function prepareResult(): void
    {
        $category = $this->getCategory($this->arParams['SECTION_CODE']);
        if (!$category) {
            $this->abortResultCache();
            Tools::process404('', true, true, true);
        }

        $this->setSeo($category);
        $this->arResult['CATEGORY'] = $category;
    }

    /**
     * @param string $slug
     *
     * @return null|Category
     */
    protected function getCategory(string $slug): ?Category
    {
        return (new CategoryQuery())
            ->withFilterParameter('CNT_ACTIVE', 'Y')
            ->withFilterParameter('CODE', $slug)
            ->withCountElements(true)
            ->withNav(['nTopCount' => 1])
            ->exec()
            ->first();
    }

    /**
     * @param Category $category
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setSeo(?Category $category): self
    {
        if (!$category || $this->arParams['SET_TITLE'] !== 'Y') {
            return $this;
        }

        global $APPLICATION;

        $cache = (new BitrixCache())
            ->withId(__METHOD__ . $category->getId())
            ->withTime(3600);

        $properties = $cache->resultOf(function () use ($category) {
            return \array_map(function ($meta) use ($category) {
                try {
                    $minPrice = $this->arParams['MIN_PRICE_PRODUCT']->getOffersSorted()->first()->getCatalogPrice();
                } catch (\Throwable $e) {
                    /**
                     * Нам неважно, почему здесь отвалилось. Важно, что мы просто не получим минимальную цену.
                     */
                    $minPrice = 0;
                }

                return \str_replace(
                    [
                        '#MIN_PRICE#',
                        '#PRODUCT_COUNT#',
                        '#PET_TYPE#'
                    ],
                    [
                        $minPrice,
                        $this->arParams['PRODUCT_COUNT'],
                        $this->getSuffix($category)
                    ],
                    $meta
                );
            }, (new SectionValues($category->getIblockId(), $category->getId()))->getValues());
        });

        if($properties['SECTION_PAGE_TITLE'] == null || $properties['SECTION_PAGE_TITLE'] == ''){
            $APPLICATION->SetTitle($properties['SECTION_META_TITLE']);
        } else {
            $APPLICATION->SetTitle($properties['SECTION_PAGE_TITLE']);
        }
        $APPLICATION->SetPageProperty('title', $properties['SECTION_META_TITLE']);
        $APPLICATION->SetPageProperty('keywords', $properties['SECTION_META_KEYWORDS']);
        $APPLICATION->SetPageProperty('description', $properties['SECTION_META_DESCRIPTION']);
        $APPLICATION->SetPageProperty('canonical', $category->getSectionPageUrl());

        return $this;
    }

    /**
     * @param Category|null $category
     *
     * @return string
     */
    protected function getSuffix(?Category $category): string
    {
        if (null === $category) {
            return '';
        }

        return $category->getSuffix() ?: $this->getSuffix($category->getParent());
    }
}
