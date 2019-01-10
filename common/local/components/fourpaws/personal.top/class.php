<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use FourPaws\SaleBundle\Service\BasketService;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetTopComponent extends FourPawsComponent
{
    /**
     * @var CurrentUserProviderInterface
     */
    private $curUserProvider;

    /**
     * @var BasketService
     */
    private $basketService;

    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $container = App::getInstance()->getContainer();
        $this->curUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->basketService = $container->get(BasketService::class);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['FUSER_ID'] = $params['FUSER_ID'] ?? $this->curUserProvider->getCurrentFUserId();
        $params['USER_ID'] = $params['USER_ID'] ?? $this->curUserProvider->getCurrentUserId();
        $params['CACHE_TYPE'] = 'N';
        $params['COUNT_ITEMS'] = $params['COUNT_ITEMS'] ?? 10;

        return $params;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentException
     * @throws \Exception
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     * @throws LoaderException
     */
    public function prepareResult(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        if (!$this->curUserProvider->getCurrentUserId()) {
            define('NEED_AUTH', true);
            return;
        }

        $offerIds = $this->basketService->getPopularOfferIds($this->arParams['COUNT_ITEMS']);
        $offers = $this->getAllOffers($offerIds);

        /**
         * сортировка по убыванию цены
         */
        uasort($offers, function (Offer $offer1, Offer $offer2) {
            return $offer2->getPrice() <=> $offer1->getPrice();
        });

        $products = $this->getAllProducts($offers);

        if (!$offers || !$products) {
            $this->setTemplatePage('notItems');
        } else {
            $this->arResult['PRODUCTS'] = $products;
            $this->arResult['OFFERS'] = $offers;
        }
    }

    /**
     * @param int[] $offerIds
     *
     * @return Offer[]
     */
    protected function getAllOffers(array $offerIds): array
    {
        $result = [];
        foreach ($offerIds as $offerId) {
            if ($offer = OfferQuery::getById($offerId)) {
                $result[$offer->getId()] = $offer;
            }
        }

        return $result;
    }

    /**
     * @param Offer[] $offers
     *
     * @return Product[]
     */
    protected function getAllProducts(array $offers): array
    {
        $result = [];
        foreach ($offers as $offer) {
            if ($product = ProductQuery::getById($offer->getCml2Link())) {
                $result[$offer->getId()] = $product;
            }
        }

        return $result;
    }
}
