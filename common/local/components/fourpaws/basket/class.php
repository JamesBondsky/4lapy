<?php
/**
 * Created by PhpStorm.
 * Date: 26.12.2017
 * Time: 18:04
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */
declare(strict_types=1);

namespace FourPaws\Components;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Order;
use FourPaws\App\Application;
use FourPaws\SaleBundle\Service\BasketService;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class BasketComponent
 * @package FourPaws\Components
 */
class BasketComponent extends \CBitrixComponent
{
    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     *
     *
     * @param Basket|null $basket
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return mixed|void
     */
    public function executeComponent(Basket $basket = null)
    {

        try {
            Loader::includeModule('sale');
            Loader::includeModule('catalog');
        } catch (LoaderException $e) {
            ShowError($e->getMessage());
            return;
        }

        if (null === $basket) {
            $app = Application::getInstance();
            $basketService = $app->getContainer()->get(BasketService::class);
            $basket = $basketService->getBasket();
            $order = Order::create('s1');
            $order->setBasket($basket);
        }
        $this->arResult['BASKET'] = $basket;
        $page = '';
        if (!$basket->count()) {
            $page = 'empty';
        }
        $this->includeComponentTemplate($page);
    }


}