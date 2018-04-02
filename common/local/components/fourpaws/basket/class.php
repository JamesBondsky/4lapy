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
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemCollection;
use Bitrix\Sale\Order;
use CBitrixComponent;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponSessionStorage;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
/** @noinspection EfferentObjectCouplingInspection */

/**
 * Class BasketComponent
 * @package FourPaws\Components
 */
class BasketComponent extends CBitrixComponent
{
    public $basketService;
    /** @var OfferCollection */
    public $offerCollection;
    /**
     * @var UserService
     */
    private $currentUserService;
    /**
     * @var ManzanaPosService
     */
    private $manzanaPosService;
    /** @var array $images */
    private $images;
    /**
     * @var CouponSessionStorage
     */
    private $couponsStorage;

    private $promoDescriptions = [];

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
        $this->couponsStorage = $container->get(CouponStorageInterface::class);
        $this->manzanaPosService = $container->get('manzana.pos.service');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @return void
     *
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws SystemException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws \InvalidArgumentException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ExecuteException
     */
    public function executeComponent(): void
    {
        /** @var Basket $basket */
        $basket = $this->arParams['BASKET'];
        if (null === $basket || !\is_object($basket) || !($basket instanceof Basket)) {
            $basket = $this->basketService->getBasket();
        }

        /** оставляем в малой корзине для актуализации */
        $this->offerCollection = $this->basketService->getOfferCollection();
        $this->setItems($basket);

        // привязывать к заказу нужно для расчета скидок
        if (null === $order = $basket->getOrder()) {
            $order = Order::create(SITE_ID);
            $order->setBasket($basket);
        }

        $this->arResult['BASKET'] = $basket;
        $this->loadPromoDescriptions();
        if (!$this->arParams['MINI_BASKET']) {
            $this->setCoupon();
            $this->arResult['USER'] = null;
            $this->arResult['USER_ACCOUNT'] = null;
            try {
                $user = $this->currentUserService->getCurrentUser();
                $this->arResult['USER'] = $user;
                $orderableBasket = $basket->getOrderableItems();
                $this->arResult['MAX_BONUS_SUM'] = 0;
                if (!$orderableBasket->isEmpty()) {
                    $chequeRequest = $this->manzanaPosService->buildRequestFromBasket(
                        $orderableBasket,
                        $user->getDiscountCardNumber()
                    );
                    $chequeRequest->setPaidByBonus($orderableBasket->getPrice());
                    $cheque = $this->manzanaPosService->processCheque($chequeRequest);
                    $this->arResult['MAX_BONUS_SUM'] = \floor($cheque->getAvailablePayment());
                }
            }  /** @noinspection BadExceptionsProcessingInspection */
            catch (NotAuthorizedException|ExecuteException $e) {
                /** в случае ошибки не показываем бюджет в большой корзине */
            }
            $this->arResult['POSSIBLE_GIFT_GROUPS'] = Gift::getPossibleGiftGroups($order);
            $this->arResult['POSSIBLE_GIFTS'] = Gift::getPossibleGifts($order);
            $this->calcTemplateFields();
            $this->checkSelectedGifts();
        }

        $this->loadImages();
        $this->includeComponentTemplate($this->getPage());
    }

    /**
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
        $result = null;
        /** @var Offer $item */
        foreach ($this->offerCollection as $item) {
            if ($item->getId() === $id) {
                $result = $item;
                break;
            }
        }
        return $result;
    }

    /**
     * @param Basket $basket
     *
     * @return Basket|bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws RuntimeException
     * @throws ApplicationCreateException
     * @throws ObjectNotFoundException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws Exception
     */
    private function setItems($basket)
    {
        $isUpdate = false;
        $notAllowedItems = new ArrayCollection();
        $fastOrderClass = null;
        /** @var BasketItem $basketItem */
        if (!$this->arParams['MINI_BASKET']) {
            $this->arResult['OFFER_MIN_DELIVERY'] = [];

            /** @todo пока берем ближайшую доставку из быстрого заказа */
            CBitrixComponent::includeComponentClass('fourpaws:fast.order');
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            try {
                $fastOrderClass = new FourPawsFastOrderComponent();
            } /** @noinspection PhpRedundantCatchClauseInspection */
            catch (SystemException $e) {
                $fastOrderClass = null;
                $logger = LoggerFactory::create('system');
                $logger->error('Ошибка загрузки компонента - ' . $e->getMessage());
            }
        }

        $haveOrder = $basket->getOrder() instanceof Order;

        foreach ($basket->getBasketItems() as $basketItem) {
            if ($basketItem->getId() === 0 || $basketItem->getProductId() === 0) {
                /** удаляет непонятно что в корзине */
                if (!$haveOrder) {
                    $basketItem->delete();
                    $isUpdate = true;
                }
                continue;
            }
            $offer = $this->getOffer((int)$basketItem->getProductId());
            $useOffer = $offer instanceof Offer && $offer->getId() > 0;
            if (!$useOffer) {
                /** если нет офера удаляем товар из корзины */
                if (!$haveOrder) {
                    $basketItem->delete();
                    $isUpdate = true;
                }
                continue;
            }

            $offerQuantity = $offer->getQuantity();
            if ($basketItem->canBuy() && !$basketItem->isDelay()) {
                if (!$haveOrder && ($offerQuantity === 0)) {
                    $basketItem->setField('DELAY', 'Y');

                    $isUpdate = true;
                }
            } else {
                if (!$haveOrder && $offerQuantity > 0 && $offerQuantity > $basketItem->getQuantity() && $basketItem->isDelay()) {
                    $basketItem->setField('DELAY', 'N');

                    $isUpdate = true;
                } else {
                    if (!$this->arParams['MINI_BASKET']) {
                        $notAllowedItems->add($basketItem);
                        /** @todo пока берем ближайшую доставку из быстрого заказа */
                        if ($fastOrderClass instanceof FourPawsFastOrderComponent && $offer->isByRequest()) {
                            $this->arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()] = $fastOrderClass->getDeliveryDate($offer,
                                true);
                        }
                    }
                }
            }

            if (!$this->arParams['MINI_BASKET'] &&
                $offer->isByRequest()
            ) {
                /** @todo пока берем ближайшую доставку из быстрого заказа */
                if ($fastOrderClass instanceof FourPawsFastOrderComponent) {
                    $this->arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()] = $fastOrderClass->getDeliveryDate($offer,
                        true);
                }

                if (!$notAllowedItems->contains($basketItem)) {
                    $notAllowedItems->add($basketItem);
                }
            }
        }

        if (!$this->arParams['MINI_BASKET']) {
            $this->arResult['NOT_ALOWED_ITEMS'] = $notAllowedItems;
        }

        if ($isUpdate && !($basket->getOrder() instanceof Order)) {
            $basket->save();
        }
        unset($isUpdate);

        return true;
    }

    /**
     *
     *
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws RuntimeException
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
                    throw new RuntimeException('TODO');
                }

                /** @noinspection PhpUndefinedMethodInspection */
                $this->arResult['SELECTED_GIFTS'][$group['discountId']] = $this->basketService
                    ->getAdder('gift')->getExistGifts($group['discountId'], true);
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
        if (!$this->arParams['MINI_BASKET'] && !$basket->count()) {
            $page = 'empty';
        }
        return $page;
    }

    /**
     * Подгружает названия и ссылки на описания акций по XML_ID
     *
     */
    private function loadPromoDescriptions()
    {
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var Order $order */
        $order = $basket->getOrder();
        $applyResult = $order->getDiscount()->getApplyResult(true);
        if (\is_array($applyResult['DISCOUNT_LIST'])) {
            $discountMap = array_column($applyResult['DISCOUNT_LIST'], 'REAL_DISCOUNT_ID', 'ID');
            $res = \CIBlockElement::GetList(
                ['ID' => 'ASC'],
                [
                    'PROPERTY_BASKET_RULES' => array_values($discountMap),
                    'IBLOCK_CODE' => IblockCode::SHARES,
                    'IBLOCK_TYPE' => IblockType::PUBLICATION
                ],
                false,
                false,
                ['NAME', 'DETAIL_PAGE_URL', 'PROPERTY_BASKET_RULES']
            );
            while ($elem = $res->GetNext()) {
                if (\is_array($elem['PROPERTY_BASKET_RULES_VALUE'])) {
                    foreach ($elem['PROPERTY_BASKET_RULES_VALUE'] as $ruleId) {
                        $this->promoDescriptions[$ruleId] = [
                            'url' => $elem['DETAIL_PAGE_URL'],
                            'name' => $elem['NAME']
                        ];
                    }
                }
            }
        }
    }

    /**
     *
     *
     * @param BasketItem $basketItem
     *
     * @return array
     */
    public function getPromoLink(BasketItem $basketItem): array
    {
        $result = [];
        /**
         * @var BasketItemCollection $basketItemCollection
         * @var Order $order
         */
        if (
            ($basketItemCollection = $basketItem->getCollection())
            &&
            ($order = $basketItemCollection->getOrder())
            &&
            ($discount = $order->getDiscount())
            &&
            ($applyResult = $discount->getApplyResult())
            &&
            \is_array($applyResult['RESULT']['BASKET'])
            &&
            isset($applyResult['RESULT']['BASKET'][$basketItem->getId()])
        ) {
            foreach (array_column($applyResult['RESULT']['BASKET'][$basketItem->getId()], 'DISCOUNT_ID') as $fakeId) {
                if ($this->promoDescriptions[$applyResult['DISCOUNT_LIST'][$fakeId]['REAL_DISCOUNT_ID']]) {
                    $result[] = $this->promoDescriptions[$applyResult['DISCOUNT_LIST'][$fakeId]['REAL_DISCOUNT_ID']];
                }
            }
        }
        return $result;
    }
    /**
     * Set coupon and coupon discount
     *
     * @return void
     */
    private function setCoupon(): void
    {
        $this->arResult['COUPON'] = $this->couponsStorage->getApplicableCoupon() ?? '';
        $this->arResult['COUPON_DISCOUNT'] = $this->basketService->getPromocodeDiscount();
    }
}
