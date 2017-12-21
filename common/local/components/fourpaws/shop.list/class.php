<?php
declare(strict_types = 1);

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global \CDatabase $DB */
/** @global \CUser $USER */

/** @global \CMain $APPLICATION */

use FourPaws\App\Application as App;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsShopListComponent extends CBitrixComponent
{
    /**
     * {@inheritdoc}
     */
    public function executeComponent()
    {
        /** @var StoreService $storeService */
        $storeService = App::getInstance()->getContainer()->get('store.service');
        
        /** @var \FourPaws\UserBundle\Service\UserService $userService */
        $userService = App::getInstance()
                                  ->getContainer()
                                  ->get('FourPaws\UserBundle\Service\UserCitySelectInterface');
        
        $city = $userService->getSelectedCity();
        if ($this->startResultCache(false, ['location' => $city['CODE']])) {
            $this->arResult['CITY'] = $city['NAME'];
            $stores                   = $storeService->getByCurrentLocation();
            $this->arResult['STORES'] = $stores->toArray();
            
            $servicesIDS = [];
            $metroIDS    = [];
            /** @var Store $store */
            foreach ($this->arResult['STORES'] as $store) {
                $servicesIDS = array_merge($servicesIDS, $store->getServices());
                $metro = $store->getMetro();
                if($metro > 0) {
                    $metroIDS[] = $metro;
                }
            }
            
            if (!empty($servicesIDS)) {
                $this->arResult['SERVICES'] = $storeService->getServicesInfo(['ID' => array_unique($servicesIDS)]);
            }
            
            if (!empty($metroIDS)) {
                $this->arResult['METRO'] = $storeService->getMetroInfo(['ID' => array_unique($metroIDS)]);
            }
            
            $this->includeComponentTemplate();
        }
        
        return true;
    }
}
