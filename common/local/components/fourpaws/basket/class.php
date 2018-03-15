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

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use CBitrixComponent;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class BasketComponent
 * @package FourPaws\Components
 */
class BasketComponent extends \CBitrixComponent
{
    public $basketService;
    /** @var OfferCollection */
    public $offerCollection;
    /**
     * @var UserService
     */
    private $currentUserService;
    /**
     * @var UserAccountService
     */
    private $userAccountService;
    /** @var array $images */
    private $images;

    /**
     * BasketComponent constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $container = Application::getInstance()->getContainer();

        $this->basketService = $container->get(BasketService::class);
        $this->currentUserService = $container->get(CurrentUserProviderInterface::class);
        $this->userAccountService = $container->get(UserAccountService::class);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     *
     * @throws ApplicationCreateException
     * @throws \Exception
     * @throws SystemException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     *
     * @return void
     */
    public function executeComponent():void
    {
        /** @var Basket $basket */
        $basket = $this->arParams['BASKET'];
        if (null === $basket || !\is_object($basket) || !($basket instanceof Basket)) {
            $basket = $this->basketService->getBasket();
        }
        $this->offerCollection = $this->basketService->getOfferCollection();

        $this->setItems($basket);

        // привязывать к заказу нужно для расчета скидок
        if (null === $order = $basket->getOrder()) {
            $order = Order::create(SITE_ID);
            $order->setBasket($basket);
        }

        $this->arResult['USER'] = null;
        $this->arResult['USER_ACCOUNT'] = null;
        try {
            $this->arResult['USER'] = $this->currentUserService->getCurrentUser();
            $this->arResult['USER_ACCOUNT'] = $this->userAccountService->findAccountByUser($this->arResult['USER']);
        } catch (NotAuthorizedException $e) {
        } catch (NotFoundException $e) {
        }
        $this->arResult['BASKET'] = $basket;
        $this->arResult['POSSIBLE_GIFT_GROUPS'] = Gift::getPossibleGiftGroups($order);
        $this->arResult['POSSIBLE_GIFTS'] = Gift::getPossibleGifts($order);
        $this->calcTemplateFields();
        $this->loadImages();
        $this->checkSelectedGifts();
        $this->includeComponentTemplate($this->getPage());
    }

    /**
     * @param Basket $basket
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws ObjectNotFoundException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Exception
     */
    private function setItems(&$basket): void
    {
        $notAlowedItems = new ArrayCollection();
        $orderableItems = new ArrayCollection();
        /** @var BasketItem $basketItem */
        $isUpdate = false;

        $this->arResult['OFFER_MIN_DELIVERY'] = [];

        /** @todo пока берем ближайшую доставку из быстрого заказа */
        \CBitrixComponent::includeComponentClass('fourpaws:fast.order');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        try {
            $fastOrderClass = new FourPawsFastOrderComponent();
        } catch (SystemException $e) {
            $fastOrderClass = null;
            $logger = LoggerFactory::create('system');
            $logger->error('Ошибка загрузки компонента - ' . $e->getMessage());
        }

        foreach ($basket->getBasketItems() as $basketItem) {
            if ($basketItem->getId() === 0 || $basketItem->getProductId() === 0) {
                /** удаляет непонятно что в корзине */
                $basketItem->delete();
                $isUpdate = true;
                continue;
            }
            $offer = $this->getOffer((int)$basketItem->getProductId());
            $useOffer = $offer instanceof Offer && $offer->getId() > 0;
            if (!$useOffer) {
                /** если нет офера удаляем товар из корзины */
                $basketItem->delete();
                $isUpdate = true;
                continue;
            }

            if($basketItem->canBuy() && !$basketItem->isDelay()){
                if ($offer->isByRequest() || $offer->getQuantity() === 0) {
                    $basketItem->setField('DELAY', 'Y');

                    $notAlowedItems->add($basketItem);
                    /** @todo пока берем ближайшую доставку из быстрого заказа */
                    if ($fastOrderClass instanceof FourPawsFastOrderComponent && $offer->isByRequest()) {
                        $this->arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()] = $fastOrderClass->getDeliveryDate($offer, true);
                    }

                    $isUpdate = true;
                }
                else{
                    $orderableItems->add($basketItem);
                }
            }
            else{
                $offerQuantity = $offer->getQuantity();
                if ($offerQuantity > 0 && $offerQuantity > $basketItem->getQuantity() && $basketItem->isDelay()
                    && !$offer->isByRequest()) {
                    $basketItem->setField('DELAY', 'N');

                    $orderableItems->add($basketItem);

                    $isUpdate = true;
                }
                else{
                    $notAlowedItems->add($basketItem);
                    /** @todo пока берем ближайшую доставку из быстрого заказа */
                    if ($fastOrderClass instanceof FourPawsFastOrderComponent && $offer->isByRequest()) {
                        $this->arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()] = $fastOrderClass->getDeliveryDate($offer, true);
                    }
                }
            }
        }
        if ($isUpdate) {
            $basket->save();
        }
        unset($isUpdate);

        $this->arResult['NOT_ALOWED_ITEMS'] = $notAlowedItems;
        $this->arResult['ORDERABLE_ITEMS'] = $orderableItems;
    }

    /**
     *
     *
     * @param $offerId
     *
     * @return ResizeImageDecorator|null
     */
    public function getImage($offerId): ?ResizeImageDecorator
    {
        return $this->images[$offerId] ?? null;
    }

    /**
     * @return UserService
     */
    public function getCurrentUserService(): UserService
    {
        return $this->currentUserService;
    }

    /**
     * @param int $id
     *
     * @return Offer|null
     */
    public function getOffer(int $id): ?Offer
    {
        /** @var Offer $item */
        foreach ($this->offerCollection as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }
        return null;
    }

    /**
     *
     *
     * @throws \RuntimeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    private function checkSelectedGifts(): void
    {
        $this->arResult['SELECTED_GIFTS'] = [];
        if (\is_array($this->arResult['POSSIBLE_GIFT_GROUPS']) && !empty($this->arResult['POSSIBLE_GIFT_GROUPS'])) {
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

    /**
     *
     * @throws \InvalidArgumentException
     */
    private function loadImages(): void
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

    private function calcTemplateFields(): void
    {
        $weight = 0;
        $quantity = 0;
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var BasketItem $basketItem */
        $orderableBasket = $basket->getOrderableItems();
        foreach ($orderableBasket as $basketItem) {
            $weight += (float)$basketItem->getWeight();
            $quantity += (int)$basketItem->getQuantity();
        }
        $this->arResult['BASKET_WEIGHT'] = $weight;
        $this->arResult['TOTAL_QUANTITY'] = $quantity;
        $this->arResult['TOTAL_DISCOUNT'] = $orderableBasket->getBasePrice() - $orderableBasket->getPrice();
        $this->arResult['TOTAL_PRICE'] = $orderableBasket->getPrice();
        $this->arResult['TOTAL_BASE_PRICE'] = $orderableBasket->getBasePrice();
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
