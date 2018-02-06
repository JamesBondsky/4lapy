<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsShopListComponent extends CBitrixComponent
{
    /** @var StoreService $storeService */
    protected $storeService;
    
    /** @var DeliveryService $deliveryService */
    protected $deliveryService;
    
    /** @var  CalculationResult */
    protected $pickupDelivery;
    
    /** @var UserService $userService */
    private $userService;
    
    /** @var Offer[] $offers */
    private $offers;
    
    /**
     * FourPawsShopListComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(\CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
            
            $this->storeService    = $container->get('store.service');
            $this->userService     = $container->get(UserCitySelectInterface::class);
            $this->deliveryService = $container->get('delivery.service');
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
    }
    
    /**
     * {@inheritdoc}
     * @throws NotAuthorizedException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws \Exception
     */
    public function executeComponent()
    {
        $container          = App::getInstance()->getContainer();
        $this->storeService = $container->get('store.service');
        
        $this->userService = $container->get(UserCitySelectInterface::class);
        
        $city = $this->userService->getSelectedCity();
        if ($this->startResultCache(false, ['location' => $city['CODE']])) {
            $this->prepareResult();
            
            $this->includeComponentTemplate();
        }
        
        return true;
    }
    
    protected function prepareResult()
    {
        $city = $this->userService->getSelectedCity();
        
        $this->arResult['CITY']      = $city['NAME'];
        $this->arResult['CITY_CODE'] = $city['CODE'];
        
        $this->arResult['SERVICES'] = $this->storeService->getServicesInfo();
        $this->arResult['METRO']    = $this->storeService->getMetroInfo();
    }
    
    /**
     * @param array $params
     *
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws FileNotFoundException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @return array
     */
    public function getStores(array $params = []) : array
    {
        $storeRepository  = $this->storeService->getRepository();
        $params['filter'] =
            array_merge((array)$params['filter'], $this->storeService->getTypeFilter($this->storeService::TYPE_SHOP));
        
        /** @var StoreCollection $storeCollection */
        $storeCollection = $storeRepository->findBy($params['filter'], $params['order']);
        if (!isset($params['returnActiveServices']) || !is_bool($params['returnActiveServices'])) {
            $params['returnActiveServices'] = false;
        }
        if (!isset($params['returnSort']) || !is_bool($params['returnSort'])) {
            $params['returnSort'] = false;
        }
        if (!isset($params['sortVal'])) {
            $params['sortVal'] = '';
        }
        
        return $this->getFormatedStoreByCollection(
            [
                'storeCollection'      => $storeCollection,
                'returnActiveServices' => $params['returnActiveServices'],
                'returnSort'           => $params['returnSort'],
                'sortVal'              => $params['sortVal'],
            ]
        );
    }
    
    /**
     * @param array $params
     *
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     * @throws FileNotFoundException
     * @return array
     */
    public function getFormatedStoreByCollection(
        array $params
    ) : array
    {
        $result = [];
        /** @var StoreCollection $storeCollection */
        $storeCollection = $params['storeCollection'];
        if (!$storeCollection->isEmpty()) {
            list($servicesList, $metroList) = $this->getFullStoreInfo($storeCollection);
            
            $stockResult = null;
            $storeAmount = 0;
            if ($this->pickupDelivery) {
                $stockResult = $this->getStockResult($this->pickupDelivery);
                $storeAmount = reset($this->offers)->getStocks()
                                                   ->filterByStores(
                                                       $this->storeService->getByCurrentLocation(
                                                           StoreService::TYPE_STORE
                                                       )
                                                   )->getTotalAmount();
            }
            
            /** @var Store $store */
            $avgGpsN = 0;
            $avgGpsS = 0;
    
            $sortHtml='';
            if($params['returnSort']) {
                $sortHtml = '<option value="" disabled="disabled">выберите</option>';
                $sortHtml .= '<option value="address" ' . ($params['sortVal']
                                                           === 'address' ? ' selected="selected" ' : '')
                             . '>по адресу</option>';
            }
            $haveMetro = false;
            foreach ($storeCollection as $store) {
                $metro   = $store->getMetro();
                $address = $store->getAddress();
                
                if (!empty($metro) && !$haveMetro) {
                    $haveMetro = true;
                }
                
                $image    = $store->getImageId();
                $imageSrc = '';
                if (!empty($image) && is_numeric($image) && $image > 0) {
                    $imageSrc =
                        CropImageDecorator::createFromPrimary($image)->setCropWidth(630)->setCropHeight(360)->getSrc();
                }
                
                $services = [];
                if (\is_array($servicesList) && !empty($servicesList)) {
                    foreach ($servicesList as $service) {
                        $services[] = $service['UF_NAME'];
                    }
                }
                
                $gpsS = $store->getLongitude();
                $gpsN = $store->getLatitude();
                if ($gpsN > 0) {
                    $avgGpsN += $gpsN;
                }
                if ($gpsS > 0) {
                    $avgGpsS += $gpsS;
                }
                
                $item = [
                    'id'         => $store->getXmlId(),
                    'addr'       => $address,
                    'adress'     => $store->getDescription(),
                    'phone'      => $store->getPhone(),
                    'schedule'   => $store->getSchedule(),
                    'photo'      => $imageSrc,
                    'metro'      => !empty($metro) ? 'м. ' . $metroList[$metro]['UF_NAME'] : '',
                    'metroClass' => !empty($metro) ? '--' . $metroList[$metro]['UF_CLASS'] : '',
                    'services'   => $services,
                    'gps_s'      => $gpsN,
                    //revert $gpsS
                    'gps_n'      => $gpsS,
                    //revert $gpsN
                ];
                
                if ($stockResult) {
                    /** @var StockResult $stockResultByStore */
                    $stockResultByStore = $stockResult->filterByStore($store)->first();
                    $amount             = $storeAmount + $stockResultByStore->getOffer()
                                                                            ->getStocks()
                                                                            ->filterByStore($store)
                                                                            ->getTotalAmount();
                    $item['amount']     = $amount > 5 ? 'много' : 'мало';
                    $item['pickup']     = DeliveryTimeHelper::showTime(
                        $this->pickupDelivery,
                        $stockResultByStore->getDeliveryDate(),
                        [
                            'SHOW_TIME' => true,
                            'SHORT'     => true,
                        ]
                    );
                }
                $result['items'][] = $item;
            }
            if($haveMetro && $params['returnSort']) {
                $sortHtml .= '<option value="metro" ' . ($params['sortVal']
                                                         === 'metro' ? ' selected="selected" ' : '')
                             . '>по метро</option>';
            }
            $countStores         = $storeCollection->count();
            $result['avg_gps_s'] = $avgGpsN / $countStores; //revert $avgGpsS
            $result['avg_gps_n'] = $avgGpsS / $countStores; //revert $avgGpsN
            $result['sortHtml']  = $sortHtml;
            if ($params['returnActiveServices']) {
                $result['services'] = $servicesList;
            }
        }
        
        return $result;
    }

    public function getActiveStoresByProduct(int $offerId): StoreCollection
    {
        $this->getOfferById($offerId);
        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return new StoreCollection();
        }

        try {
            return $this->getStockResult($pickupDelivery)->getStores();
        } catch (NotFoundException $e) {
            return new StoreCollection();
        }
    }

    /**
     *
     * @param StoreCollection $stores
     *
     * @throws \Exception
     * @return array
     */
    public function getFullStoreInfo(StoreCollection $stores) : array
    {
        $servicesIds = [];
        $metroIds    = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $servicesIds = array_merge($servicesIds, $store->getServices());
            $metro       = $store->getMetro();
            if ($metro > 0) {
                $metroIds[] = $metro;
            }
        }
        $services = [];
        if (!empty($servicesIds)) {
            $services = $this->storeService->getServicesInfo(['ID' => array_unique($servicesIds)]);
        }
        
        $metro = [];
        if (!empty($metroIds)) {
            $metro = $this->storeService->getMetroInfo(['ID' => array_unique($metroIds)]);
        }
        
        return [
            $services,
            $metro,
        ];
    }
    
    /**
     * @return bool|StockResultCollection
     */
    protected function getStockResult(CalculationResult $delivery)
    {
        return $this->deliveryService->getStockResultByDelivery($delivery);
    }
    
    /**
     * @param int $offerId
     *
     * @return Offer
     */
    protected function getOfferById(int $offerId) : Offer
    {
        if (!isset($this->offers[$offerId])) {
            $offerQuery = new OfferQuery();
            $offerQuery->withFilter(['ID' => $offerId]);
            $this->offers[$offerId] = $offerQuery->exec()->first();
        }
        
        return $this->offers[$offerId];
    }
    
    /**
     * @return CalculationResult|null
     */
    protected function getPickupDelivery()
    {
        if (!$this->pickupDelivery) {
            $deliveries = $this->deliveryService->getByProduct(reset($this->offers));
            
            foreach ($deliveries as $delivery) {
                if ($this->deliveryService->isInnerPickup($delivery)) {
                    $this->pickupDelivery = $delivery;
                    break;
                }
            }
        }
        
        return $this->pickupDelivery;
    }
    
    /**
     * @param Request $request
     *
     * @return array
     */
    public function getFilterByRequest(Request $request) : array
    {
        $result     = [];
        $storesSort = $request->get('stores-sort');
        if (\is_array($storesSort) && !empty($storesSort)) {
            $result['UF_SERVICES'] = $storesSort;
        }
        $code = $request->get('code');
        if (!empty($code)) {
            $result['UF_LOCATION'] = $code;
        }
        $search = $request->get('search');
        if (!empty($search)) {
            $result[] = [
                'LOGIC'          => 'OR',
                '%ADDRESS'       => $search,
                '%METRO.UF_NAME' => $search,
            ];
        }
        
        return $result;
    }
    
    /**
     * @param Request $request
     *
     * @return array
     */
    public function getOrderByRequest(Request $request) : array
    {
        $result = [];
        $sort   = $request->get('sort');
        if (!empty($sort)) {
            switch ($sort) {
                case 'city':
                    $result = ['LOCATION.NAME.NAME' => 'asc'];
                    break;
                case 'address':
                    $result = ['ADDRESS' => 'asc'];
                    break;
                case 'metro':
                    $result = ['METRO.UF_NAME' => 'asc'];
                    break;
            }
        }
        
        return $result;
    }
}
