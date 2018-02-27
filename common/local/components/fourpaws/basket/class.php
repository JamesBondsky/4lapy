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
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class BasketComponent
 * @package FourPaws\Components
 */
class BasketComponent extends \CBitrixComponent
{
    /**
     * @var UserService
     */
    private $currentUserService;

    public $basketService;

    /** @var array $images */
    private $images;
    /** @var OfferCollection */
    public $offerCollection;

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
        $container = Application::getInstance()->getContainer();

        $this->basketService = $container->get(BasketService::class);
        $this->currentUserService = $container->get(CurrentUserProviderInterface::class);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
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
        $this->calcTemplateFields();
        $this->loadImages();
        $this->checkSelectedGifts();
        $this->includeComponentTemplate($this->getPage());
    }

    /**
     *
     *
     * @throws \RuntimeException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    private function checkSelectedGifts()
    {
        $this->arResult['SELECTED_GIFTS'] = [];
        if (\is_array($this->arResult['POSSIBLE_GIFT_GROUPS']) and !empty($this->arResult['POSSIBLE_GIFT_GROUPS'])) {
            foreach ($this->arResult['POSSIBLE_GIFT_GROUPS'] as $group) {
                if (\count($group) === 1) {
                    $group = current($group);
                } else {
                    throw new \RuntimeException('TODO');
                }

                $this->arResult['SELECTED_GIFTS'][$group['discountId']] = $this->basketService
                    ->getAdder()->getExistGifts($group['discountId'], true);
            }
        }
    }

    private function loadImages()
    {
        /** @var Offer $item */
        foreach ($this->offerCollection as $item) {
            if (isset($this->images[$item->getId()])) {
                continue;
            }
            /** @var ResizeImageCollection $images */
            $images = $item->getResizeImages(110, 110);
            $this->images[$item->getId()] = $images->first();
        }
    }

    private function calcTemplateFields()
    {
        $weight = 0;
        $quantity = 0;
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var BasketItem $basketItem */
        foreach ($basket->getOrderableItems() as $basketItem) {
            $weight += (float)$basketItem->getWeight();
            $quantity += (int)$basketItem->getQuantity();
        }
        $this->arResult['BASKET_WEIGHT'] = $weight;
        $this->arResult['TOTAL_QUANTITY'] = $quantity;
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

    /**
     * @return UserService
     */
    public function getCurrentUserService(): UserService
    {
        return $this->currentUserService;
    }
}
