<?php
use \Bitrix\Iblock\Component\ElementList;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Catalog\Query\ProductQuery;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsCatalogPopularProducts extends ElementList
{
    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->setPaginationMode(false);
        $this->setExtendedMode(false);
        $this->setMultiIblockMode(false);
    }

    public function onPrepareComponentParams($params)
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 3600;
        }

        $params['IBLOCK_TYPE'] = IblockType::CATALOG;
        $params['IBLOCK_ID'] = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        $params['CACHE_GROUPS'] = 'N';
        $params['PAGE_ELEMENT_COUNT'] = isset($params['PAGE_ELEMENT_COUNT']) ? intval($params['PAGE_ELEMENT_COUNT']) : 0;
        $params['PAGE_ELEMENT_COUNT'] = $params['PAGE_ELEMENT_COUNT'] > 0 ?: 10;

        $params = parent::onPrepareComponentParams($params);

        // отложенная генерация результата (через ajax)
        $params['DEFFERED_LOAD'] = 'Y';
        // может быть задействован при дполнительной фильтрации по секции
        $this->arParams['DEPTH'] = 5;
        $this->arParams['RCM_TYPE'] = 'personal';

        return $params;
    }

    public function executeComponent()
    {
        $this->setAction($this->prepareAction());
        $this->doAction();
    }

    /**
     * Action preparing to execute in doAction method with postfix "Action".
     * E.g. action "initialLoad" calls "initialLoadAction".
     *
     * @return string
     */
    protected function prepareAction()
    {
        $action = 'initialLoad';
        if ($this->request->isAjaxRequest() && $this->request->get('action') === 'deferredLoad')  {
            $action = 'bigDataLoad';
        }
        return $action;
    }

    /**
     * Action executor.
     */
    protected function doAction()
    {
        $action = $this->getAction();
        if (is_callable(array($this, $action.'Action')))
        {
            call_user_func(array($this, $action.'Action'));
        }
    }

    /**
     * This method executes when "initialLoad" action is chosen.
     */
    protected function initialLoadAction()
    {
        $this->arResult['BIG_DATA'] = $this->getBigDataInfo();
        if ($this->arParams['DEFFERED_LOAD'] === 'Y') {
            //
        } else {
            // to do
        }
        $this->loadData();
    }

    /**
     * This method executes when "bigDataLoad" action is chosen.
     */
    protected function bigDataLoadAction()
    {
        $this->productIdMap = [];
        $ids = $this->getProductIds();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $this->productIdMap[$id] = $id;
            }
        }
        $this->loadData();
    }

    /**
     * Show cached component data or load if outdated.
     */
    public function loadData($ids = [])
    {
        //if ($this->isCacheDisabled() || $this->startResultCache(false, $this->getAdditionalCacheId(), $this->getComponentCachePath())) {
            $this->arResult['PRODUCTS'] = $this->getProducts(array_keys($this->productIdMap));
            $this->includeComponentTemplate();
        //}
    }

    /**
     * @param array $ids
     *
     * @return \FourPaws\BitrixOrm\Collection\CollectionBase|null
     */
    protected function getProducts($ids)
    {
        if (empty($ids)) {
            return null;
        }
        return (new ProductQuery())
            ->withFilterParameter('ID', $ids)
            ->exec();
    }

    /**
     * Return array of iblock element ids to show for "bigDataLoad" action.
     *
     * @return array
     */
    protected function getProductIds()
    {
        $this->arParams['FILTER_IDS'] = [];

        // general filter
        $this->filterFields = $this->getFilter();

        // try cloud
        $ids = $this->getBigDataResponseRecommendation();

        // try bestsellers
        if (count($ids) < $this->arParams['PAGE_ELEMENT_COUNT']) {
            // параметры, используемые getBestSellersRecommendation
            $this->arParams['FILTER'] = array('PAYED');
            $this->arParams['PERIOD'] = 30;
            $this->arParams['BY'] = 'AMOUNT';
            $ids = $this->getBestSellersRecommendation($ids);
        }

        // try most viewed
        if (count($ids) < $this->arParams['PAGE_ELEMENT_COUNT']) {
            $ids = $this->getMostViewedRecommendation($ids);
        }

        // try random
        if (count($ids) < $this->arParams['PAGE_ELEMENT_COUNT']) {
            $ids = $this->getRandomRecommendation($ids);
        }

        // limit
        $ids = array_slice($ids, 0, $this->arParams['PAGE_ELEMENT_COUNT']);

        return $ids;
    }

    /**
     * Return filter fields to execute.
     *
     * @return array
     */
    protected function getFilter()
    {
        return array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE_DATE' => 'Y',
            'ACTIVE' => 'Y',
        );
    }

    /**
     * Возвращает id элементов из ответа bigdata
     *
     * @return array
     */
    protected function getBigDataResponseRecommendation()
    {
        $ids = $this->request->get('items') ?: [];
        if (!empty($ids)) {
            $recommendationId = $this->request->get('rid');
            $ids = $this->filterByParams($ids, $this->arParams['FILTER_IDS'], false);

            foreach ($ids as $id) {
                $this->recommendationIdToProduct[$id] = $recommendationId;
            }
        }
        return $ids;
    }

    /**
     * Return array of big data settings.
     *
     * @return array
     */
    protected function getBigDataInfo()
    {
        return array(
            'enabled' => true,
            'count' => $this->arParams['PAGE_ELEMENT_COUNT'],
            'js' => array(
                'cookiePrefix' => \COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM'),
                'cookieDomain' => $GLOBALS['APPLICATION']->GetCookieDomain(),
                'serverTime' => time()
            ),
            'params' => $this->getBigDataServiceRequestParams($this->arParams['RCM_TYPE'])
        );
    }

}
