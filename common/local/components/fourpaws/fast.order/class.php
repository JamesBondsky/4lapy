<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Components;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Order;
use Bitrix\Sale\UserMessageException;
use CBitrixComponent;
use Exception;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\EcommerceBundle\Preset\Bitrix\SalePreset;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\SaleBundle\Exception\InvalidArgumentException as SaleInvalidArgumentException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserService;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class FourPawsFastOrderComponent
 * @package FourPaws\Components
 */
class FourPawsFastOrderComponent extends CBitrixComponent
{
    /** @var OfferCollection */
    public $offerCollection;
    /** @var UserAuthorizationInterface */
    private $authUserProvider;
    /** @var UserAuthorizationInterface */
    private $currentUserProvider;
    /** @var BasketService */
    private $basketService;
    /** @var array $images */
    private $images;
    /**
     * @var GoogleEcommerceService
     */
    private $ecommerceService;
    /**
     * @var SalePreset
     */
    private $salePreset;
    /**
     * @var DeliveryService
     */
    private $deliveryService;

    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|CBitrixComponent $component
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $container = App::getInstance()->getContainer();

        $this->authUserProvider = $container->get(UserAuthorizationInterface::class);
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->basketService = $container->get(BasketService::class);
        $this->ecommerceService = $container->get(GoogleEcommerceService::class);
        $this->deliveryService = $container->get(DeliveryService::class);
        $this->salePreset = $container->get(SalePreset::class);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params = parent::onPrepareComponentParams($params);

        $params['PATH_TO_CATALOG'] = '/catalog/';
        $params['TYPE'] = !empty($params['TYPE']) ? $params['TYPE'] : '';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?: 360000;

        return $params;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotImplementedException
     * @throws ArgumentNullException
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws SaleInvalidArgumentException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws EmptyEntityClass
     * @throws SystemException
     * @throws IblockNotFoundException
     * @throws ObjectException
     * @throws ArgumentException
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        if ($this->arParams['TYPE']) {
            if ($this->arParams['TYPE'] === 'innerForm') {
                $this->arResult['IS_AUTH'] = $this->authUserProvider->isAuthorized();
                if ($this->authUserProvider->isAuthorized()) {
                    try {
                        $this->arResult['CUR_USER'] = $this->currentUserProvider->getCurrentUser();
                    } catch (NotAuthorizedException | UsernameNotFoundException $e) {
                        /** никогда не сработает */
                    }
                }
                $basket = $this->basketService->getBasket();
                $this->offerCollection = $this->basketService->getOfferCollection(true);
                // привязывать к заказу нужно для расчета скидок
                if (null === $order = $basket->getOrder()) {
                    $order = Order::create(SITE_ID);
                    $order->setBasket($basket);
                }
                $this->arResult['BASKET'] = $basket;
                $this->loadImages();
                $this->calcTemplateFields();
            }

            $orderId = (int)$this->request->get('orderId');
            if ($orderId) {
                $order = Order::load($orderId);
                $this->arResult['ECOMMERCE_VIEW_SCRIPT'] = $this->ecommerceService->renderScript(
                    $this->salePreset->createPurchaseFromBitrixOrder($order, 'Покупка в 1 клик'),
                    true
                );
            }

            $this->includeComponentTemplate($this->arParams['TYPE']);
        } else {
            if ($this->startResultCache($this->arParams['CACHE_TIME'])) {
                $this->includeComponentTemplate();
            }
        }

        parent::executeComponent();
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
     * @param Offer $offer
     * @param bool $showToday
     *
     * @return string
     *
     * @throws UserMessageException
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws NotFoundException
     * @throws DeliveryNotFoundException
     * @throws ArgumentException
     * @throws ApplicationCreateException
     */
    public function getDeliveryDate(Offer $offer, $showToday = false): string
    {
        /** Если доставка сегодня не показываем */
        $dates = [];
        $deliveryDate = '';
        $res = $this->deliveryService->getByProduct($offer);

        foreach ($res as $item) {
            $periodType = $item->getPeriodType();
            if ($periodType === CalculationResult::PERIOD_TYPE_DAY || $periodType === CalculationResult::PERIOD_TYPE_MONTH) {
                $periodFrom = $item->getPeriodFrom();
                switch ($periodFrom) {
                    case 0:
                        $dates[0] = $showToday ? 'Сегодня' : '';
                        break;
                    case 1:
                        $dates[1] = 'Завтра';
                        break;
                    default:
                        $date = Date::createFromTimestamp(time());
                        $date->add($item->getPeriodFrom() . $periodType);
                        $dates[$periodFrom] = $date;
                }
            } else {
                $dates[0] = $showToday ? 'Сегодня' : '';
            }
        }

        if (!empty($dates)) {
            /** @var Date $minDate */
            $minDate = $dates[min(array_keys($dates))];
            if ($minDate instanceof Date) {
                $deliveryDate = $minDate->format('d.m.Y');
            } else {
                $deliveryDate = $minDate;
            }
        }

        return $deliveryDate;
    }

    /**
     *
     * @throws InvalidArgumentException
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
            /** @var ResizeImageDecorator $image */
            foreach ($images as $image) {
                if (empty($image->getSrc())) {
                    continue;
                }
                $this->images[$item->getId()] = $image;
                break;
            }
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
            $quantity += (int)$basketItem->getQuantity();
            $weight += (float)$basketItem->getWeight() * (int)$basketItem->getQuantity();
        }
        $this->arResult['BASKET_WEIGHT'] = $weight;
        $this->arResult['TOTAL_QUANTITY'] = $quantity;
        $this->arResult['TOTAL_DISCOUNT'] = $orderableBasket->getBasePrice() - $orderableBasket->getPrice();
        $this->arResult['TOTAL_PRICE'] = $orderableBasket->getPrice();
        $this->arResult['TOTAL_BASE_PRICE'] = $orderableBasket->getBasePrice();
    }

    /**
     * @return UserService
     */
    public function getCurrentUserService(): UserService
    {
        return $this->currentUserProvider;
    }
}
