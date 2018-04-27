<?php
/**
 * Created by PhpStorm.
 * Date: 26.04.2018
 * Time: 17:50
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\Components;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Sale\BasketItem;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Offer;
use FourPaws\CatalogBundle\AjaxController\ProductInfoController;
use FourPaws\SaleBundle\Service\BasketService;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class GroupSet
 * @package FourPaws\Components
 */
class GroupSet extends CBitrixComponent
{

    protected $basketService;

    /**
     * GroupSet constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $container = Application::getInstance()->getContainer();
        $this->basketService = $container->get(BasketService::class);
    }

    public function executeComponent(): void
    {
        /** @var Offer $currentOffer */
        $currentOffer = $this->arParams['OFFER'];
        $groupSets = [];
        try {
            $groupSets = ProductInfoController::getGroupSets($currentOffer);
        } /** @noinspection PhpUndefinedClassInspection */ catch (TypeError $e) {
            echo 'Не передан предложение', PHP_EOL;
            $logger = LoggerFactory::create('productDetail');
            $logger->error($e->getMessage());
        }
        if(empty($groupSets)) {
            return;
        }

        $basket = $this->basketService->getBasket();
        $ids = [];
        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            $ids[] = $basketItem->getProductId();
        }
        $ids = array_flip(array_flip($ids));
        dump($groupSets, $ids);

        $this->includeComponentTemplate();
    }
}