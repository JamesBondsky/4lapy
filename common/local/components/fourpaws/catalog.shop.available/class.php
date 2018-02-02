<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\StoreBundle\Service\StoreService;

CBitrixComponent::includeComponentClass('fourpaws:city.delivery.info');

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCatalogShopAvailableComponent extends CBitrixComponent
{
    /**
     * @var StoreService
     */
    protected $storeService;
    
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        
        $this->storeService = Application::getInstance()->getContainer()->get('store.service');
    }
    
    /** {@inheritdoc} */
    public function onPrepareComponentParams($params) : array
    {
        if (!($params['PRODUCT'] instanceof Product) && !empty($params['PRODUCT_ID'])) {
            $productQuery      = new \FourPaws\Catalog\Query\ProductQuery();
            $params['PRODUCT'] = $productQuery->withFilter(['=ID' => $params['PRODUCT_ID']])->exec()->first();
        }
        
        if (empty($params['OFFER']) && !empty($params['OFFER_ID'])) {
            $offerQuery      = new OfferQuery();
            $params['OFFER'] = $offerQuery->withFilter(['=ID' => $params['OFFER_ID']])->exec()->first();
        }
        if (empty($params['OFFER']) && $params['PRODUCT'] instanceof Product) {
            $params['OFFER'] = $params['PRODUCT']->getOffers()->first();
        }
        
        return $params;
    }
    
    /**
     * {@inheritdoc}
     */
    public function executeComponent()
    {
        parent::executeComponent();
        
        if ($this->startResultCache()) {
            $this->includeComponentTemplate();
        }
        
        return true;
    }
}
