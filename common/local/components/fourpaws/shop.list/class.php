<?php

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
use FourPaws\StoreBundle\Service\StoreService;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsShopListComponent extends CBitrixComponent
{
    /**
     * {@inheritdoc}
     */
    public function executeComponent()
    {
        if ($this->startResultCache(false, [])) {
            /** @var StoreService $storeService */
            $storeService = App::getInstance()->getContainer()->get('store.service');
    
            $this->arResult['STORES'] = $storeService->getByCurrentLocation();
            if($this->arResult['STORES'] instanceof \FourPaws\StoreBundle\Entity\Store){
                $this->arResult['STORES'] = [$this->arResult['STORES']];
            }
            
            $this->includeComponentTemplate();
        }
        
        
        
        return true;
    }
}
