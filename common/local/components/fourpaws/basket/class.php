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

use Bitrix\Sale\Basket;
use Bitrix\Sale\Order;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Service\BasketService;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class BasketComponent
 * @package FourPaws\Components
 */
class BasketComponent extends \CBitrixComponent
{
    private $basketService;
    /** @var array $images */
    private $images;
    /** @var OfferCollection */
    private $offerCollection;

    /**
     * BasketComponent constructor.
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

        $this->basketService = Application::getInstance()->getContainer()->get(BasketService::class);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     *
     * @return void
     */
    public function executeComponent()
    {
        /** @var Basket $basket */
        $basket = $this->arParams['BASKET'];
        if (null === $basket || !\is_object($basket) || !($basket instanceof Basket)) {
            $basket = $this->basketService->getBasket();
        }
        // привязывать к заказу нужно для расчета скидок
        if (null === $order = $basket->getOrder()) {
            $order = Order::create(SITE_ID);
            $order->setBasket($basket);
        }
        $this->arResult['BASKET'] = $basket;
        $this->arResult['POSSIBLE_GIFT_GROUPS'] = Gift::getPossibleGiftGroups($order);
        $this->arResult['POSSIBLE_GIFTS'] = Gift::getPossibleGifts($order);
        $this->offerCollection = $this->basketService->getOfferCollection();
        $this->loadImages();
        $this->includeComponentTemplate($this->getPage());
    }

    private function loadImages()
    {
        foreach ($this->offerCollection as $item) {
            if (isset($this->images[$item->getId()])) {
                continue;
            }
            /**
             * @var ResizeImageCollection $images
             * @var ResizeImageDecorator $image
             */
            $images = $item->getResizeImages(110, 110);
            $this->images[$item->getId()] = $images->first();
        }
    }

    /**
     *
     *
     * @param $offerId
     *
     * @return ResizeImageDecorator|null
     */
    public function getImage($offerId)
    {
        return $this->images[$offerId] ?? null;
    }

    /**
     *
     *
     * @return string
     */
    private function getPage(): string
    {
        $page = '';
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        if (!$basket->count()) {
            $page = 'empty';
        }
        return $page;
    }
}