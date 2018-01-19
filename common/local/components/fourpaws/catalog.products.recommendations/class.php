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

class FourPawsCatalogProductsRecommendations extends ElementList
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

        // Y - отложенная генерация результата (через ajax)
        $params['DEFERRED_LOAD'] = isset($params['DEFERRED_LOAD']) && $params['DEFERRED_LOAD'] === 'Y' ? 'Y' : 'N';

        // может быть задействован при дополнительной фильтрации по секции
        $params['DEPTH'] = 5;
        $params['AJAX_ID'] = isset($params['AJAX_ID']) ? $params['AJAX_ID'] : '';

        // id товара, для которого подбираются похожие
        $params['RCM_PROD_ID'] = isset($params['RCM_PROD_ID']) ? intval($params['RCM_PROD_ID']) : 0;
        // id товаров, для которых сервисом BigData подбирается рекомендация по алгоритму postcross
        $params['POSTCROSS_IDS'] = isset($params['POSTCROSS_IDS']) && is_array($params['POSTCROSS_IDS']) ? $params['POSTCROSS_IDS'] : [];

        // тип запрашиваемой рекомендации BigData
        //   personal - персональная рекомендация
        //   similar_view - похожие просматриваемые товары (требуется значение RCM_PROD_ID)
        //   similar_sell - похожие покупаемые товары (требуется значение RCM_PROD_ID)
        //   similar - похожие товары по комбинированному методу (требуется значение RCM_PROD_ID)
        //   bestsell - топ товаров на сайте по заказам
        //   postcross - рекомендуемые для покупки товары? (требуется значение POSTCROSS_IDS)
        $params['RCM_TYPE'] = isset($params['RCM_TYPE']) ? trim($params['RCM_TYPE']) : 'personal';

        // использовать ли сервис BigData
        $params['USE_BIG_DATA'] = isset($params['USE_BIG_DATA']) && $params['USE_BIG_DATA'] === 'N' ? 'N' : 'Y';
        // использовать ли подбор по топу продаваемых товаров
        $params['USE_BESTSELLERS'] = isset($params['USE_BESTSELLERS']) && $params['USE_BESTSELLERS'] === 'N' ? 'N' : 'Y';
        // использовать ли подбор по топу просмтариваемых товаров
        $params['USE_MOST_VIEWED'] = isset($params['USE_MOST_VIEWED']) && $params['USE_MOST_VIEWED'] === 'N' ? 'N' : 'Y';
        // использовать ли рандомный подбор
        $params['USE_RANDOM'] = isset($params['USE_RANDOM']) && $params['USE_RANDOM'] === 'N' ? 'N' : 'Y';
        // использовать ли подбор по покупаемым вместе товарам
        $params['USE_SAME_PURCHASE'] = isset($params['USE_SAME_PURCHASE']) && $params['USE_SAME_PURCHASE'] === 'N' ? 'N' : 'Y';
        if ($params['RCM_PROD_ID'] <= 0) {
            $params['USE_SAME_PURCHASE'] = 'N';
        }

        $params = parent::onPrepareComponentParams($params);

        return $params;
    }

    public function executeComponent()
    {
        $this->recommendationIdToProduct = [];
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
            $action = 'deferredLoad';
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
            if ($this->arParams['USE_BIG_DATA'] === 'Y') {
                $this->arResult['BIG_DATA_SETTINGS'] = $this->getBigDataSettings();
            }
        } else {
            $this->arResult['RESULT_TYPE'] = 'RESULT';
            if ($this->arParams['USE_BIG_DATA'] === 'Y') {
                $this->doBigDataRequest();
            }
            $this->initProductIds();
        }
        $this->loadData();
    }

    /**
     * This method executes when "deferredLoad" action is chosen.
     */
    protected function deferredLoadAction()
    {
        $this->arResult['RESULT_TYPE'] = 'RESULT';

        if ($this->arParams['USE_BIG_DATA'] === 'Y') {
            $this->arResult['BIG_DATA_RESPONSE']['ITEMS'] = $this->request->get('items');
            if (!is_array($this->arResult['BIG_DATA_RESPONSE']['ITEMS'])) {
                $this->arResult['BIG_DATA_RESPONSE']['ITEMS'] = [];
            }
            $this->arResult['BIG_DATA_RESPONSE']['RECOMMENDATION_ID'] = trim($this->request->get('rid'));
        }

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
        $httpClient->setHeader('User-Agent', 'X-Bitrix-Sale');
        $response = $httpClient->get($this->arResult['BIG_DATA_SETTINGS']['requestBaseUrl'].'?'.$this->arResult['BIG_DATA_SETTINGS']['requestUrlParams']);
        if ($httpClient->getStatus() == 200) {
            $response = $response ? Json::decode($response) : [];
            if (isset($response['id'])) {
                $this->arResult['BIG_DATA_RESPONSE']['RECOMMENDATION_ID'] = trim($response['id']);
            }
            if (isset($response['items']) && is_array($response['items'])) {
                $this->arResult['BIG_DATA_RESPONSE']['ITEMS'] = $response['items'];
            }
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
            'enabled' => $this->arParams['USE_BIG_DATA'] === 'Y',
            'count' => $this->arParams['PAGE_ELEMENT_COUNT'],
            'js' => [
                'cookiePrefix' => \COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM'),
                'cookieDomain' => $GLOBALS['APPLICATION']->GetCookieDomain(),
                'serverTime' => time()
            ],
            'params' => $this->getBigDataServiceRequestParams($this->arParams['RCM_TYPE']),
        ];
        $settings['requestUrlParams'] = http_build_query($settings['params']);

        return $settings;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getBigDataServiceRequestParams($type = '')
    {
        $params = parent::getBigDataServiceRequestParams($type);
        if ($type === 'postcross') {
            $params = [
                'aid' => $params['aid'],
                'op' => 'postcross',
                'eids' => implode(',', $this->arParams['POSTCROSS_IDS']),
                'count' => $params['count']
            ];
        }
        return $params;
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
        if ($this->arParams['RCM_PROD_ID'] > 0) {
            $this->arParams['FILTER_IDS'][] = $this->arParams['RCM_PROD_ID'];
        }
        if (!empty($this->arParams['POSTCROSS_IDS'])) {
            $this->arParams['FILTER_IDS'] = array_merge($this->arParams['FILTER_IDS'], $this->arParams['POSTCROSS_IDS']);
        }

        // general filter
        $this->filterFields = $this->getFilter();

        $ids = [];

        // try cloud
        if ($this->arParams['USE_BIG_DATA'] === 'Y') {
            $ids = $this->getBigDataResponseRecommendation();
        }

        // покупались вместе с товаром
        if ($this->arParams['USE_SAME_PURCHASE'] === 'Y') {
            if (count($ids) < $this->arParams['PAGE_ELEMENT_COUNT']) {
                $ids = $this->getSamePurchaseProductIds($ids);
            }
        }

        // самые продаваемые
        if ($this->arParams['USE_BESTSELLERS'] === 'Y') {
            if (count($ids) < $this->arParams['PAGE_ELEMENT_COUNT']) {
                // параметры, используемые getBestSellersRecommendation
                $this->arParams['FILTER'] = ['PAYED'];
                $this->arParams['PERIOD'] = 30;
                $this->arParams['BY'] = 'AMOUNT';
                $ids = $this->getBestSellersRecommendation($ids);
            }
        }

        // самые просматриваемые
        if ($this->arParams['USE_MOST_VIEWED'] === 'Y') {
            if (count($ids) < $this->arParams['PAGE_ELEMENT_COUNT']) {
                $ids = $this->getMostViewedRecommendation($ids);
            }
        }

        // рандом
        if ($this->arParams['USE_RANDOM'] === 'Y') {
            if (count($ids) < $this->arParams['PAGE_ELEMENT_COUNT']) {
                $ids = $this->getRandomRecommendation($ids);
            }
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
     * Возвращает id товаров, которые покупались вместе с заданным товаром
     *
     * @param array $ids Products id.
     * @return array
     */
    protected function getSamePurchaseProductIds($ids)
    {
        $recommendationId = 'purchase';
        $productIds = [];

        $getParentOnly = true;
        $productIterator = \CSaleProduct::GetProductList(
            $this->arParams['RCM_PROD_ID'],
            $this->arParams['MIN_BUYES'],
            $this->arParams['PAGE_ELEMENT_COUNT'],
            $getParentOnly
        );

        if($productIterator) {
            $GLOBALS['CACHE_MANAGER']->RegisterTag('sale_product_buy');
            while($product = $productIterator->fetch()) {
                $productIds[] = $product['PARENT_PRODUCT_ID'];
            }
            $productIds = $this->filterByParams($productIds, $this->arParams['FILTER_IDS'], false);

            foreach ($productIds as $id) {
                if (!isset($this->recommendationIdToProduct[$id])) {
                    $this->recommendationIdToProduct[$id] = $recommendationId;
                }
            }
        }
        return array_unique(array_merge($ids, $productIds));
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
