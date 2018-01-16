<?php

use \Bitrix\Iblock\Component\ElementList;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
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
        $params['CACHE_TYPE'] = isset($params['CACHE_TYPE']) ? $params['CACHE_TYPE'] : 'A';
        $params['CACHE_TIME'] = isset($params['CACHE_TIME']) ? $params['CACHE_TIME'] : 3600;

        $params['IBLOCK_TYPE'] = IblockType::CATALOG;
        $params['IBLOCK_ID'] = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        $params['CACHE_GROUPS'] = 'N';
        $params['PAGE_ELEMENT_COUNT'] = isset($params['PAGE_ELEMENT_COUNT']) ? intval($params['PAGE_ELEMENT_COUNT']) : 0;
        $params['PAGE_ELEMENT_COUNT'] = $params['PAGE_ELEMENT_COUNT'] > 0 ? $params['PAGE_ELEMENT_COUNT'] : 10;

        $params = parent::onPrepareComponentParams($params);
        $arParams['AJAX_ID'] = isset($params['AJAX_ID']) ? $params['AJAX_ID'] : '';

        // отложенная генерация результата (через ajax)
        $params['DEFERRED_LOAD'] = 'Y';
        // тип запрашиваемой рекомендации BigData
        $this->arParams['RCM_TYPE'] = 'personal';
        // может быть задействован при дополнительной фильтрации по секции
        $this->arParams['DEPTH'] = 5;

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
        if (is_callable(array($this, $action.'Action'))) {
            call_user_func(array($this, $action.'Action'));
        }
    }

    /**
     * This method executes when "initialLoad" action is chosen.
     */
    protected function initialLoadAction()
    {
        $this->arResult['RESULT_TYPE'] = 'INITIAL';
        if ($this->arParams['DEFERRED_LOAD'] === 'Y') {
            $this->arResult['BIG_DATA_SETTINGS'] = $this->getBigDataSettings();
        } else {
            $this->arResult['RESULT_TYPE'] = 'RESULT';
            $this->doBigDataRequest();
            $this->initProductIds();
        }
        $this->loadData();
    }

    /**
     * This method executes when "bigDataLoad" action is chosen.
     */
    protected function bigDataLoadAction()
    {
        $this->arResult['RESULT_TYPE'] = 'RESULT';

        $this->arResult['BIG_DATA_RESPONSE']['ITEMS'] = $this->request->get('items');
        if (!is_array($this->arResult['BIG_DATA_RESPONSE']['ITEMS'])) {
            $this->arResult['BIG_DATA_RESPONSE']['ITEMS'] = [];
        }
        $this->arResult['BIG_DATA_RESPONSE']['RECOMMENDATION_ID'] = trim($this->request->get('rid'));

        $this->initProductIds();

        $this->loadData();
    }

    /**
     * Отправляет запрос BigData
     */
    protected function doBigDataRequest()
    {
        $this->arResult['BIG_DATA_SETTINGS'] = $this->getBigDataSettings();
        $this->arResult['BIG_DATA_RESPONSE']['ITEMS'] = [];
        $this->arResult['BIG_DATA_RESPONSE']['RECOMMENDATION_ID'] = '';

        $httpClient = new HttpClient();
        $httpClient->setHeader('CMS', 'Bitrix');
        $response = $httpClient->get($this->arResult['BIG_DATA_SETTINGS']['requestBaseUrl'].'?'.$this->arResult['BIG_DATA_SETTINGS']['requestUrlParams']);
        $response = $response ? Json::decode($response) : [];
        if (isset($response['id'])) {
            $this->arResult['BIG_DATA_RESPONSE']['RECOMMENDATION_ID'] = trim($response['id']);
        }
        if (isset($response['items']) && is_array($response['items'])) {
            $this->arResult['BIG_DATA_RESPONSE']['ITEMS'] = $response['items'];
        }
    }

    /**
     * Return array of big data settings.
     *
     * @return array
     */
    protected function getBigDataSettings()
    {
        $settings = [
            'requestBaseUrl' => 'https://analytics.bitrix.info/crecoms/v1_0/recoms.php',
            'requestUrlParams' => '',
            'enabled' => true,
            'count' => $this->arParams['PAGE_ELEMENT_COUNT'],
            'js' => [
                'cookiePrefix' => \COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM'),
                'cookieDomain' => $GLOBALS['APPLICATION']->GetCookieDomain(),
                'serverTime' => time()
            ],
            'params' => $this->getBigDataServiceRequestParams($this->arParams['RCM_TYPE']),
        ];

        switch ($settings['params']['op']) {
            case 'recommend':
                $settings['requestUrlParams'] = str_replace(
                    [
                        '#OP#',
                        '#UID#',
                        '#COUNT#',
                        '#AID#',
                        '#IB#',
                    ],
                    [
                        $settings['params']['op'],
                        $settings['params']['uid'],
                        $settings['params']['count'],
                        $settings['params']['aid'],
                        $settings['params']['ib'],
                    ],
                    'op=#OP#&uid=#UID#&count=#COUNT#&aid=#AID#&ib=#IB#'
                );
                break;
        }

        return $settings;
    }

    /**
     * Show cached component data or load if outdated.
     */
    public function loadData()
    {
        // нужно для генерации уникального идентификатора кеша
        $this->productIdMap = [];
        if (!empty($this->arResult['ids'])) {
            foreach ($this->arResult['ids'] as $id) {
                $this->productIdMap[$id] = $id;
            }
        }
        $this->arParams['RESULT_TYPE'] = $this->arResult['RESULT_TYPE'];

        if ($this->isCacheDisabled() || $this->startResultCache(false, $this->getAdditionalCacheId(), $this->getComponentCachePath())) {
            $this->arResult['PRODUCTS'] = $this->getProducts($this->arResult['ids']);
            $this->endResultCache();
        }

        $this->arResult['recommendationIdToProduct'] = $this->recommendationIdToProduct;

        $this->includeComponentTemplate();
    }

    /**
     * Заполнение списка id элементов для дальнейшей их выборки
     */
    protected function initProductIds()
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
        $this->arResult['ids'] = array_slice($ids, 0, $this->arParams['PAGE_ELEMENT_COUNT']);
    }

    /**
     * Return filter fields to execute.
     *
     * @return array
     */
    protected function getFilter()
    {
        return [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE_DATE' => 'Y',
            'ACTIVE' => 'Y',
        ];
    }

    /**
     * Возвращает отфильтрованные id элементов по ответу BigData
     *
     * @return array
     */
    protected function getBigDataResponseRecommendation()
    {
        $ids = [];
        if (!empty($this->arResult['BIG_DATA_RESPONSE']['ITEMS'])) {
            $ids = $this->filterByParams($this->arResult['BIG_DATA_RESPONSE']['ITEMS'], $this->arParams['FILTER_IDS'], false);
            foreach ($ids as $id) {
                $this->recommendationIdToProduct[$id] = $this->arResult['BIG_DATA_RESPONSE']['RECOMMENDATION_ID'];
            }
        }
        return $ids;
    }

    /**
     * @param array $ids
     * @return array
     */
    protected function getProducts($ids)
    {
        $result = [];
        if (empty($ids)) {
            return $result;
        }
        $productQuery = new ProductQuery();
        $productQuery->withFilterParameter('ID', $ids);
        $productQueryCollection = $productQuery->exec();
        foreach ($productQueryCollection as $product) {
            $result[] = $product;
        }
        return $result;
    }
}
