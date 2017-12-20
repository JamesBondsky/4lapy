<?php

namespace FourPaws\Components;

use Bitrix\Iblock\Component\Tools;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;

class CatalogCategory extends \CBitrixComponent
{
    public function onPrepareComponentParams($params): array
    {
        $params['CODE'] = $params['CODE'] ?: '';
        $params['CODE'] = (string)$params['CODE'];

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        if (!$this->arParams['CODE']) {
            Tools::process404('', true, true, true);
        }

        if ($this->startResultCache()) {
            parent::executeComponent();

            $category = $this->getCategory($this->arParams['CODE']);

            if (!$category) {
                $this->abortResultCache();
                Tools::process404('', true, true, true);
            }
            $this->includeComponentTemplate();
        }
        return $this->arResult['CATEGORY'];
    }

    /**
     * @param string $code
     * @return null|Category
     */
    protected function getCategory(string $code)
    {
        return (new CategoryQuery())
            ->withFilterParameter('CODE', $code)
            ->withFilterParameter('CNT_ACTIVE', 'Y')
            ->withFilterParameter('>ELEMENT_CNT', 0)
            ->withNav(['nTopCount' => 1])
            ->withCountElements(true)
            ->exec()
            ->first();
    }
}
