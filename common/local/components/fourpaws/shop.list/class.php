<?php

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use Symfony\Component\HttpFoundation\Request;

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global \CDatabase $DB */
/** @global \CUser $USER */

/** @global \CMain $APPLICATION */

/** @noinspection AutoloadingIssuesInspection */
class FourPawsShopListComponent extends CBitrixComponent
{
    /** @var StoreService $storeService */
    private $storeService;
    
    /** @var \FourPaws\UserBundle\Service\UserService $userService */
    private $userService;
    
    /**
     * FourPawsShopListComponent constructor.
     *
     * @param \CBitrixComponent|null $component
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \RuntimeException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public function executeComponent()
    {
        /** @var StoreService $storeService */
        $this->storeService = App::getInstance()->getContainer()->get('store.service');
        
        /** @var \FourPaws\UserBundle\Service\UserService $userService */
        $this->userService = App::getInstance()->getContainer()->get(UserCitySelectInterface::class);
        
        $city = $this->userService->getSelectedCity();
        if ($this->startResultCache(false, ['location' => $city['CODE']])) {
            $this->arResult['CITY']   = $city['NAME'];
            $stores                   = $storeService->getByCurrentLocation();
            $this->arResult['STORES'] = $stores->toArray();
            
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
     * @return array
     * @throws \Exception
     */
    public function getFullStoreInfo(array $stores) : array
    {
        $servicesIDS = [];
        $metroIDS    = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $servicesIDS = array_merge($servicesIDS, $store->getServices());
            $metro       = $store->getMetro();
            if ($metro > 0) {
                $metroIDS[] = $metro;
            }
        }
        $services = [];
        if (!empty($servicesIDS)) {
            $services = $this->storeService->getServicesInfo(['ID' => array_unique($servicesIDS)]);
        }
        
        $metro = [];
        if (!empty($metroIDS)) {
            $metro = $this->storeService->getMetroInfo(['ID' => array_unique($metroIDS)]);
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
     * @return array
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Exception
     */
    public function getStores(array $filter = [], array $order = []) : array
    {
        $result          = [];
        $storeRepository = $this->storeService->getRepository();
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
                
                $gpsS              = $store->getLongitude();
                $gpsN              = $store->getLatitude();
                $avgGpsN           += $gpsN;
                $avgGpsS           += $gpsS;
                $result['items'][] = [
                    'adress'     => $address,
                    'phone'      => $store->getPhone(),
                    'schedule'   => $store->getSchedule(),
                    'photo'      => $imageSrc,
                    'metroClass' => 'col--blue',
                    'services'   => $services,
                    'gps_s'      => $gpsS,
                    'gps_n'      => $gpsN,
                ];
            }
            $countStores         = count($stores);
            $result['avg_gps_s'] = $avgGpsS / $countStores;
            $result['avg_gps_n'] = $avgGpsN / $countStores;
        }
        
        return $result;
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
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
            $result[] =
                [
                    'LOGIC'          => 'OR',
                    '%ADDRESS'       => $search,
                    '%METRO.UF_NAME' => $search,
                ];
        }
        
        return $result;
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
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
