<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\Catalog\Model\Offer;
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
    private $storeService;
    
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
            
            $this->storeService = $container->get('store.service');
            $this->userService  = $container->get(UserCitySelectInterface::class);
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
            $this->arResult['CITY']      = $city['NAME'];
            $this->arResult['CITY_CODE'] = $city['CODE'];
            
            $this->arResult['SERVICES'] = $this->storeService->getServicesInfo();
            $this->arResult['METRO']    = $this->storeService->getMetroInfo();
            
            $this->includeComponentTemplate();
        }
        
        return true;
    }
    
    /**
     * @param array $filter
     * @param array $order
     *
     * @throws FileNotFoundException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @return array
     */
    public function getStores(array $filter = [], array $order = [], $returnActiveServices = false) : array
    {
        $storeRepository = $this->storeService->getRepository();
        $filter          = array_merge($filter, $this->storeService->getTypeFilter($this->storeService::TYPE_SHOP));
        
        /** @var StoreCollection $storeCollection */
        $storeCollection = $storeRepository->findBy($filter, $order);
        
        return $this->getFormatedStoreByCollection($storeCollection, $returnActiveServices);
    }
    
    /**
     * @param StoreCollection $storeCollection
     * @param bool            $returnActiveServices
     *
     * @return array
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     * @throws FileNotFoundException
     */
    public function getFormatedStoreByCollection(
        StoreCollection $storeCollection,
        $returnActiveServices = false
    ) : array
    {
        $result = [];
        if (!$storeCollection->isEmpty()) {
            list($servicesList, $metroList) = $this->getFullStoreInfo($storeCollection);
            
            /** @var Store $store */
            $avgGpsN = 0;
            $avgGpsS = 0;
            foreach ($storeCollection as $store) {
                $metro   = $store->getMetro();
                $address = $store->getAddress();
                
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
                    'addr'       => $address,
                    'adress'     => $store->getDescription(),
                    'phone'      => $store->getPhone(),
                    'schedule'   => $store->getSchedule(),
                    'photo'      => $imageSrc,
                    'metro'      => !empty($metro) ? 'м. ' . $metroList[$metro]['UF_NAME'] : '',
                    'metroClass' => !empty($metro) ? 'b-delivery-list__col--' . $metroList[$metro]['UF_CLASS'] : '',
                    'services'   => $services,
                    'gps_s'      => $gpsN,
                    //revert $gpsS
                    'gps_n'      => $gpsS,
                    //revert $gpsN
                ];
                if ($store->getOfferId() > 0) {
                    $item['amount'] = $store->getOfferAmount() > 5 ? 'много' : 'мало';
                    
                    if ($store->getOfferAmount() > 0) {
                        $pickup = $this->getActiveAmoutPickupText($store->getSchedule());
                    } else {
                        /** @var DeliveryService $deliveryService */
                        $deliveryService = App::getInstance()->getContainer()->get('delivery.service');
                        /** @var \Bitrix\Sale\Delivery\CalculationResult[] $calculationResult */
                        $calculationResult        =
                            $deliveryService->getByProduct(
                                $this->getOfferById($store->getOfferId()),
                                null,
                                [$store->getId()]
                            );
                        $currentCalculationResult = current($calculationResult);
                        echo '<pre>', var_dump($currentCalculationResult), '</pre>';
                        $currentCalculationResult->getPeriodType();
                        $currentCalculationResult->getPeriodFrom();
                        $pickup = 'сегодня, с 16:00';
                    }
                    
                    $item['pickup'] = $pickup;
                }
                $result['items'][] = $item;
            }
            $countStores         = $storeCollection->count();
            $result['avg_gps_s'] = $avgGpsN / $countStores; //revert $avgGpsS
            $result['avg_gps_n'] = $avgGpsS / $countStores; //revert $avgGpsN
            if ($returnActiveServices) {
                $result['services'] = $servicesList;
            }
        }
        
        return $result;
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
    
    protected function getActiveAmoutPickupText(string $schedule)
    {
        $explode = explode('-', $schedule);
        
        $beginExplode = explode('-', $explode[0]);
        $beginHouse   = (int)trim($beginExplode[0]);
        $beginMinutes = (int)trim($beginExplode[1]);
        
        $endExplode = explode('-', $explode[1]);
        $endHouse   = (int)trim($endExplode[0]);
        $endMinutes = (int)trim($endExplode[1]);
        
        $curHouse   = date('H');
        $curMinutes = date('i');
        
        $nextHouse = $curHouse + 1;
        $nextDay   = false;
        
        if ($nextHouse > $endHouse) {
            $nextDay = true;
        } elseif ($nextHouse === $endHouse) {
            if ($endMinutes > $curMinutes) {
                $nextDay = true;
            }
        }
        
        if ($nextDay) {
            /** @todo Нужно ли накинуть час от открытия? */
            $pickup = 'завтра, с ' . $beginHouse . ':' . $beginMinutes;
        } else {
            $pickup = 'сегодня, с ' . $nextHouse . ':' . $curMinutes;
        }
        
        return $pickup;
    }
    
    protected function getNotAmountPickupText($store)
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = App::getInstance()->getContainer()->get('delivery.service');
        /** @var \Bitrix\Sale\Delivery\CalculationResult[] $calculationResult */
        $calculationResult        =
            $deliveryService->getByProduct(
                $this->getOfferById($store->getOfferId()),
                null,
                [$store->getId()]
            );
        $currentCalculationResult = current($calculationResult);
        echo '<pre>', var_dump($currentCalculationResult), '</pre>';
        $dateFrom = new DateTime('now', new DateTimeZone('Europe/Moscow'));
        $dateFrom->add($this->getTimeInterval($currentCalculationResult->getPeriodFrom(), $currentCalculationResult->getPeriodType()));
        $pickup = \FourPaws\Helpers\DateHelper::replaceRuMonth($dateFrom->format(''), \FourPaws\Helpers\DateHelper::);
        
        return $pickup;
    }
    
    protected function getOfferById(int $offerId)
    {
        if (!isset($this->offers[$offerId])) {
            $offerQuery = new \FourPaws\Catalog\Query\OfferQuery();
            $offerQuery->withFilter(['ID' => $offerId]);
            $this->offers[$offerId] = $offerQuery->exec()->first();
        }
        
        return $this->offers[$offerId];
    }
    
    /**
     * @param int $offerId
     *
     * @return StoreCollection
     * @throws \Exception
     */
    public function getActiveStoresByProduct(int $offerId) : StoreCollection
    {
        return $this->storeService->getAvailableProductStoresCurrentLocation($offerId);
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
