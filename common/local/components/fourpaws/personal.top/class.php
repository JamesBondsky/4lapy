<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Collection;
use Bitrix\Sale\Internals\BasketTable;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Cheque;
use FourPaws\External\Manzana\Model\ChequeItem;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetTopComponent extends CBitrixComponent
{
    /** @var CurrentUserProviderInterface */
    private $curUserProvider;
    
    private $itemIds;
    
    /** @var ManzanaService */
    private $manzanaService;
    
    private $sortItems;
    
    private $allProducts;
    
    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
        $this->curUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->manzanaService  = $container->get('manzana.service');
    }
    
    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params) : array
    {
        /** params */
        try {
            $params['offerIblockId'] = IblockUtils::getIblockId(
                IblockType::CATALOG,
                IblockCode::OFFERS
            );
        } catch (IblockNotFoundException $e) {
            return [];
        }
        $params['FUSER_ID']             = $params['FUSER_ID'] ?? $this->curUserProvider->getCurrentFUserId();
        $params['USER_ID']              = $params['USER_ID'] ?? $this->curUserProvider->getCurrentUserId();
        $params['CACHE_TIME']           = $params['CACHE_TIME'] ?? 360000;
        $params['COUNT_ITEMS']          = $params['COUNT_ITEMS'] ?? 10;
        $params['LIMIT_MANZANA_CHEQUE'] = 50;
        /** @todo возможно увеличить кеширование до 1-3х часов, не так часто происходят покупки */
        $params['MANZANA_CACHE_TIME']   = 2 * 60 * 60;
        
        return $params;
    }
    
    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentException
     * @throws \Exception
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        if (empty($this->arParams)) {
            return null;
        }

        $instance = Application::getInstance();
        
        $this->setFrameMode(true);
        
        /** @noinspection PhpUnhandledExceptionInspection */
        try {
            $userId = $this->curUserProvider->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);
            
            return null;
        }
        
        $offerIds                   = [];
        $offers                     = [];
        $this->sortItems            = [];
        $this->allProducts          = [];
        $this->arResult['PRODUCTS'] = [];
        $this->arResult['OFFERS']   = [];
        //кешируем выборку из манзаны и базы с первичной обработкой на 15 минут
        $cache = $instance->getCache();
        $cachePath = $this->getCachePath() ?: $this->getPath();
        if ($cache->initCache(
            $this->arParams['MANZANA_CACHE_TIME'],
            serialize(
                [
                    'USER_ID'              => $this->arParams['USER_ID'],
                    'COUNT_ITEMS'          => $this->arParams['COUNT_ITEMS'],
                    'LIMIT_MANZANA_CHEQUE' => $this->arParams['LIMIT_MANZANA_CHEQUE'],
                ]
            ),
            $cachePath
        )) {
            $vars              = $cache->getVars(); // достаем переменные из кеша
            $this->sortItems   = $vars['sortItems'];
            $this->itemIds     = $vars['itemIds'];
            $offerIds          = $vars['offerIds'];
            $this->allProducts = $vars['allProducts'];
        } elseif ($cache->startDataCache()) {
            $tagCache = null;
            if (\defined('BX_COMP_MANAGED_CACHE')) {
                $tagCache = $instance->getTaggedCache();
                $tagCache->startTagCache($cachePath);
            }
            //получение данных из манзаны
            list($xmlIds, $allItems) = $this->getXmlIdsByManzana();
            //получение товаров с сайта по XML_ID
            if (!empty($xmlIds)) {
                $offerIds = $this->getOffersByXmlIds($xmlIds);
            }
            
            $countOffers = count($offerIds);
            if ($countOffers < $this->arParams['COUNT_ITEMS']) {
                //устанавливаем кастрированные объекты по товарам из манзаны
                if (\is_array($allItems) && !empty($allItems)) {
                    foreach ($allItems as $item) {
                        if (!array_key_exists($item['XML_ID'], $offerIds)) {
                            $this->sortItems['manzana_' . $item['XML_ID']] = ['PRICE' => (float)$item['PRICE']];
                            
                            $product = new Product();
                            $product->withId(0);
                            $product->withActive(true);
                            $product->withName($item['NAME']);
                            $product->withXmlId($item['XML_ID']);
                            $this->allProducts['manzana_' . $item['XML_ID']] = $product;
                            
                            $offer = new Offer();
                            $offer->withId(0);
                            $offer->setProduct($product);
                            $offer->withPrice((float)$item['PRICE']);
                            $offer->withXmlId($item['XML_ID']);
                            $this->arResult['OFFERS']['manzana_' . $item['XML_ID']] = $offer;
                            
                            $product->setOffers([$offer]);
                        }
                    }
                }
                //делаем добор если товаров из манзаны не хватает
                if (count($allItems) < $this->arParams['COUNT_ITEMS']) {
                    //Поиск сымых продаваемых товаров пользователя на сайте
                    list(
                        $this->itemIds, $bitrixOfferIds, $sortItems
                        ) =
                        $this->getPopularItemsSite($offerIds, $this->arParams['COUNT_ITEMS'] - $countOffers);
                    $offerIds        = array_merge($offerIds, $bitrixOfferIds);
                    $this->sortItems += $sortItems;
                }
            }

            if ($tagCache !== null) {
                TaggedCacheHelper::addManagedCacheTags([
                    'personal:top',
                    'personal:top:'. $userId,
                    'order:'. $userId
                ], $tagCache);
                $tagCache->endTagCache();
            }
            
            $cache->endDataCache(
                [
                    'sortItems'   => $this->sortItems,
                    'allProducts' => $this->allProducts,
                    'itemIds'     => $this->itemIds,
                    'offerIds'    => $offerIds,
                ]
            ); // записываем в кеш
        }
        
        //кешируем вывод
        if ($this->startResultCache(
            $this->arParams['CACHE_TIME'],
            [
                'USER_ID' => $userId,
                'TYPE'    => 'PERSONAL_TOP',
                'IDS'     => array_keys($this->sortItems),
            ],
            $cachePath
        )) {
            //сортировка по цене
            Collection::sortByColumn(
                $this->sortItems,
                [
                    'PRICE' => [
                        SORT_NUMERIC,
                        SORT_ASC,
                    ],
                ],
                '',
                0,
                true
            );
            
            //получаем все торговые предложения
            if (!empty($offerIds)) {
                $offers = $this->getOffers($offerIds);
            }
            
            //получаем товары
            if (!empty($this->itemIds)) {
                /** @noinspection AdditionOperationOnArraysInspection */
                $this->allProducts += $this->getAllProducts($offers);
            }
            //Устанавливаем финализирвоанный сортирвоанный массив
            $this->setResults($this->sortItems, $this->allProducts);

            $page= '';
            if(empty($this->arResult['PRODUCTS'])){
                $page = 'notItems';
            }

            TaggedCacheHelper::addManagedCacheTags([
                'personal:top',
                'personal:top:'. $userId,
                'order:'. $userId
            ]);

            $this->includeComponentTemplate($page);
        }
        
        return true;
    }
    
    /**
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ApplicationCreateException
     * @throws ConstraintDefinitionException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @return array
     */
    private function getXmlIdsByManzana() : array
    {
        $xmlIds   = [];
        $allXmlId = [];
        $allItems = [];
        /** @var User $user */
        $user = $this->curUserProvider->getUserRepository()->find($this->arParams['USER_ID']);
        if ($user !== null && !empty($user->getPersonalPhone())) {
            try {
                $manzanaContactId = $this->manzanaService->getContactIdByUser($user);
                if (!empty($manzanaContactId)) {
                    $cheques = $this->manzanaService->getCheques($manzanaContactId);
                    if (!empty($cheques)) {
                        /** @var Cheque $cheque */
                        $i = 0;
                        foreach ($cheques as $cheque) {
                            try {
                                $chequeItems = $this->manzanaService->getItemsByCheque($cheque->chequeId);
                                $i++;
                                if (!empty($chequeItems)) {
                                    /** @var ChequeItem $chequeItem */
                                    foreach ($chequeItems as $chequeItem) {
                                        /** оставляем только товары(старые и новые) */
                                        if ((int)$chequeItem->number < 2000000) {
                                            $allXmlId[]                    = $xmlIds[] = $chequeItem->number;
                                            $allItems[$chequeItem->number] =
                                                [
                                                    'XML_ID' => $chequeItem->number,
                                                    'PRICE'  => $chequeItem->price,
                                                    'NAME'   => $chequeItem->name,
                                                ];
                                        }
                                    }
                                }
                            } catch (ManzanaServiceException $e) {
                            }
                            if ($i === $this->arParams['LIMIT_MANZANA_CHEQUE']) {
                                $xmlIds = array_unique($xmlIds);
                                if (count($xmlIds) < $this->arParams['COUNT_ITEMS']) {
                                    $i = 0;
                                } else {
                                    break;
                                }
                            }
                        }
                    }
                }
            } catch (ManzanaServiceException $e) {
            }
        }
        
        //просчет количества
        $countValues = array_count_values($allXmlId);
        //сортирповка по убыванию
        arsort($countValues, SORT_NUMERIC);
        
        //получаем количество элементов по ограничению
        if (count($countValues) > $this->arParams['COUNT_ITEMS']) {
            $chunkItems  = array_chunk($countValues, $this->arParams['COUNT_ITEMS'], true);
            $countValues = $chunkItems[0];
        }
        
        //получаем нужные элементы
        if (!empty($allItems)) {
            $allItems = array_intersect_key($allItems, $countValues);
        }
        
        //получаем нужные элементы
        if (!empty($xmlIds)) {
            $xmlIds = array_intersect(array_unique($xmlIds), array_keys($countValues));
        }
        
        return [
            $xmlIds,
            $allItems,
        ];
    }
    
    /**
     * @param array $xmlIds
     *
     * @return array
     */
    private function getOffersByXmlIds(array $xmlIds) : array
    {
        $offerIds        = [];
        $query           = new OfferQuery();
        $offerCollection = $query->withSelect(
            [
                'ID',
                'XML_ID',
                'PRICE',
            ]
        )->withFilter(
            [
                '=XML_ID' => $xmlIds,
            ]
        )->exec();
        /** @var Offer $offer */
        foreach ($offerCollection as $offer) {
            $offerIds[$offer->getXmlId()]     = $offer->getId();
            $this->sortItems[$offer->getId()] = ['PRICE' => $offer->getPrice()];
        }
        
        return $offerIds;
    }
    
    /**
     * @param array $manzanaOfferIds
     *
     * @param int   $count
     *
     * @return array
     */
    private function getPopularItemsSite(array $manzanaOfferIds = [], int $count = 0) : array
    {
        $query = BasketTable::query();
        $query->setFilter(
            [
                'FUSER_ID' => $this->arParams['FUSER_ID'],
                'ORDER_ID' > 0,
            ]
        );
        if (!empty($manzanaOfferIds)) {
            $query->addFilter('!PRODUCT_ID', $manzanaOfferIds);
        }
        $query->registerRuntimeField(
            'CNT',
            [
                'data_type'  => 'integer',
                'expression' => [
                    'COUNT(%s)',
                    'PRODUCT_ID',
                ],
            ]
        );
        $query->setOrder(
            [
                'CNT'   => 'desc',
                'PRICE' => 'desc',
            ]
        );
        $query->setSelect(
            [
                'PRODUCT_ID',
                'PRODUCT_IBLOCK_ID' => 'PRODUCT.IBLOCK.IBLOCK_ID',
                'PRICE',
            ]
        );
        $query->setCacheTtl($this->arParams['CACHE_TIME']);
        $query->setLimit($count > 0 ? $count : $this->arParams['COUNT_ITEMS']);
        $res = $query->exec();
        
        $itemIds   = [];
        $offerIds  = [];
        $sortItems = [];
        
        while ($item = $res->fetch()) {
            if ((int)$item['PRODUCT_IBLOCK_ID'] === $this->arParams['offerIblockId']) {
                //формируем массив с торговыми предложениями
                $offerIds[] = (int)$item['PRODUCT_ID'];
            } else {
                //формируем массив с товарами
                $itemIds[] = (int)$item['PRODUCT_ID'];
            }
            $sortItems[(int)$item['PRODUCT_ID']] = $item;
        }
        
        return [
            $itemIds,
            $offerIds,
            $sortItems,
        ];
    }
    
    /**
     * @param array $offerIds
     *
     * @return array
     */
    private function getOffers(array $offerIds = []) : array
    {
        $offers = [];
        if (!empty($offerIds)) {
            /** @var Offer $offer */
            foreach ($offerIds as $offerId) {
                $offer = OfferQuery::getById((int)$offerId);
                if (!\in_array($offer->getCml2Link(), $this->itemIds, true)) {
                    $this->itemIds[] = $offer->getCml2Link();
                }
                $offers[$offer->getCml2Link()][$offer->getId()] = $offer;
                $this->arResult['OFFERS'][$offer->getId()]      = $offer;
            }
        }
        
        return $offers;
    }
    
    /**
     * @param array $offers
     *
     * @return array
     */
    private function getAllProducts(array $offers = []) : array
    {
        $query    = new ProductQuery();
        $res      = $query->withFilter(['=ID' => $this->itemIds])->exec();
        $products = $res->toArray();
        //set result array
        $allProducts = [];
        /** @var Product $product */
        if (\is_array($products) && !empty($products)) {
            foreach ($products as $product) {
                $productOffers = (array)$offers[$product->getId()];
                if (count($productOffers) > 0) {
                    foreach ($productOffers as $offerId => $offer) {
                        $allProducts[$offerId] = $product;
                    }
                } else {
                    $allProducts[$product->getId()] = $product;
                }
            }
        }
        
        return $allProducts;
    }
    
    private function setResults(array $sortItems, array $allProducts)
    {
        $this->arResult['PRODUCTS'] = [];
        foreach ($sortItems as $sortId => $val) {
            foreach ($allProducts as $id => $product) {
                if ($id === $sortId) {
                    $this->arResult['PRODUCTS'][$id] = $product;
                    break;
                }
            }
        }
    }
}
