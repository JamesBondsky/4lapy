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
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use CBitrixComponent;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
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
     *
     * @param Basket|null $basket
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     *
     * @return mixed|void
     */
    public function executeComponent(Basket $basket = null)
    {
        if (null === $basket) {
            $basket = $this->basketService->getBasket();
        }
        // привязывать к заказу нужно для расчета скидок
        $order = Order::create(SITE_ID);
        $order->setBasket($basket);
        $this->arResult['BASKET'] = $basket;
        $this->arResult['POSSIBLE_GIFT_GROUPS'] = Gift::getPossibleGiftGroups($order);
        $this->arResult['POSSIBLE_GIFTS'] = Gift::getPossibleGifts($order);
        $this->loadOfferCollection();
        $this->loadImages();
        $this->includeComponentTemplate($this->getPage());
    }

    private function loadImages()
    {
        foreach ($this->offerCollection as $item) {
            if(isset($this->images[$item->getId()])) {
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

    private function loadOfferCollection() {
        $ids = [];
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            $ids[] = $basketItem->getProductId();
        }
        $ids += $this->arResult['POSSIBLE_GIFTS'];
        $ids = array_flip(array_flip(array_filter($ids)));

        if (empty($ids)) {
            return;
        }
        $this->offerCollection = (new OfferQuery())->withFilterParameter('ID', $ids)->exec();
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