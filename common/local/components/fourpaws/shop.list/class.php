<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

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
            $stores                      = $this->storeService->getByCurrentLocation($this->storeService::TYPE_SHOP);
            $this->arResult['STORES']    = $stores->toArray();
            if (!empty($this->arResult['STORES'])) {
                list(
                    $this->arResult['SERVICES'], $this->arResult['METRO']
                    ) = $this->getFullStoreInfo($this->arResult['STORES']);
            }
            
            $this->includeComponentTemplate();
        }
        
        return true;
    }
    
    /**
     *
     * @param array $stores
     *
     * @throws \Exception
     * @return array
     */
    public function getFullStoreInfo(array $stores) : array
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
            'services' => $services,
            'metro'    => $metro,
        ];
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
    public function getStores(array $filter = [], array $order = []) : array
    {
        $result          = [];
        $storeRepository = $this->storeService->getRepository();
        $filter          = array_merge($filter, $this->storeService->getTypeFilter($this->storeService::TYPE_SHOP));
        $storeCollection = $storeRepository->findBy($filter, $order);
        $stores          = $storeCollection->toArray();
        if (!empty($stores)) {
            list(
                $servicesList, $metroList
                ) = $this->getFullStoreInfo($stores);
            
            /** @var Store $store */
            $avgGpsN = 0;
            $avgGpsS = 0;
            foreach ($stores as $store) {
                $address = '';
                $metro   = $store->getMetro();
                if (!empty($metro) && is_array($metroList) && !empty($metroList) && isset($metroList[$metro])) {
                    $address .= $metroList[$metro]['UF_NAME'] . ', ';
                }
                $address .= $store->getAddress();
                
                $image    = $store->getImageId();
                $imageSrc = '';
                if (!empty($image) && is_numeric($image) && $image > 0) {
                    $imageSrc = CropImageDecorator::createFromPrimary($image)->setCropWidth(630)->setCropHeight(
                        360
                    )->getSrc();
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
                $result['items'][] = [
                    'addr'       => $address,
                    'adress'     => $store->getDescription(),
                    'phone'      => $store->getPhone(),
                    'schedule'   => $store->getSchedule(),
                    'photo'      => $imageSrc,
                    'metro'      => !empty($metro) ? $metroList[$metro]['UF_NAME'] : '',
                    'metroClass' => !empty($metro) ? 'b-delivery-list__col--' . $metroList[$metro]['UF_CLASS'] : '',
                    'services'   => $services,
                    'gps_s'      => $gpsN, //revert $gpsS
                    'gps_n'      => $gpsS, //revert $gpsN
                ];
            }
            $countStores         = count($stores);
            $result['avg_gps_s'] = $avgGpsN / $countStores; //revert $avgGpsS
            $result['avg_gps_n'] = $avgGpsS / $countStores; //revert $avgGpsN
        }
        
        return $result;
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
