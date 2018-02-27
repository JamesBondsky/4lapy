<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Components;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use \Bitrix\Main;
use \Bitrix\Sale;

/** @noinspection AutoloadingIssuesInspection */
/**
 * Class FourPawsFastOrderComponent
 * @package FourPaws\Components
 */
class FourPawsFastOrderComponent extends \CBitrixComponent
{
    /** @var UserAuthorizationInterface */
    private $authUserProvider;

    /** @var UserAuthorizationInterface */
    private $currentUserProvider;
    /** @var BasketService  */
    private $basketService;

    /** @var array $images */
    private $images;
    /** @var OfferCollection */
    public $offerCollection;

    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(\CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
        $this->authUserProvider = $container->get(UserAuthorizationInterface::class);
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->basketService = $container->get(BasketService::class);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['PATH_TO_CATALOG'] = '/catalog/';
        $params['TYPE'] = !empty($params['TYPE']) ? $params['TYPE'] : '';
        return $params;
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     * @throws EmptyEntityClass
     * @throws SystemException
     * @throws IblockNotFoundException
     * @throws ObjectException
     * @throws ArgumentException
     * @throws \Exception
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        if(!empty($this->arParams['TYPE'])) {
            if ($this->arParams['TYPE'] === 'innerForm') {
                $this->arResult['IS_AUTH'] = $this->authUserProvider->isAuthorized();
                if ($this->authUserProvider->isAuthorized()) {
                    try {
                        $this->arResult['CUR_USER'] = $this->currentUserProvider->getCurrentUser();
                    } catch (NotAuthorizedException $e) {
                        /** никогда не сработает */
                    }
                }
                $this->offerCollection = $this->basketService->getOfferCollection();
                $this->arResult['BASKET'] = $this->basketService->getBasket();
                $this->loadImages();
                $this->calcTemplateFields();
            }
            $this->includeComponentTemplate($this->arParams['TYPE']);
        }
        else{
            if($this->startResultCache(360000)) {
                $this->includeComponentTemplate();
            }
        }

        return true;
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
        $this->arResult['BASKET_WEIGHT'] = number_format($weight / 1000, 2);
        $this->arResult['TOTAL_QUANTITY'] = $quantity;
    }
}
