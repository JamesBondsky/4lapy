<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
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

    /**
     * FourPawsCatalogShopAvailableComponent constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws SystemException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        try {
            $this->storeService = Application::getInstance()->getContainer()->get('store.service');
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
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
        $params['CACHE_TIME'] = 360000;
        
        return $params;
    }
    
    /**
     * {@inheritdoc}
     */
    public function executeComponent()
    {
        parent::executeComponent();

        /** @var Offer $offer */
        $offer = $this->arParams['OFFER'];
        /** кешируем для каждого товара из-за того что в шаблрен подставляется id */
        if ($this->startResultCache($this->arParams['CACHE_TIME'], ['OFFER_ID'=>$offer->getId()])) {
            $this->includeComponentTemplate();
        }
        
        return true;
    }
}
