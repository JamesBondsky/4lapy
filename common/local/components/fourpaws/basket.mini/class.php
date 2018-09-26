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

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\SaleBundle\Exception\BasketUserInitializeException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\BasketUserService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
/** @noinspection EfferentObjectCouplingInspection */

/**
 * Class BasketComponent
 * @package FourPaws\Components
 */
class BasketMiniComponent extends FourPawsComponent
{
    /** @var BasketService */
    public $basketService;

    /** @var BasketUserService */
    protected $basketUserService;

    /** @var array */
    public $offers;

    /** @var array $images */
    private $images;
    /** @var Basket */
    private $basketItemsWithoutGifts;

    /**
     * BasketMiniComponent constructor.
     * @param CBitrixComponent|null $component
     * @throws SystemException
     * @throws \LogicException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function __construct(?CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $container = Application::getInstance()->getContainer();
        $this->basketService = $container->get(BasketService::class);
        $this->basketUserService = $container->get(BasketUserService::class);
    }

    /**
     * @param $params
     * @return array
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws BasketUserInitializeException
     */
    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 0;
        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'N';
        $params['FUSER_ID'] = $params['FUSER_ID'] ?? $this->basketUserService->getCurrentUserId();

        return parent::onPrepareComponentParams($params);
    }

    /**
     * Prepare component result
     */
    public function prepareResult(): void
    {
        /** @var Basket $basket */
        $basket = $this->basketService->getBasket(false, $this->arParams['FUSER_ID']);
        TaggedCacheHelper::addManagedCacheTag('basket:' . $basket->getFUserId());
        $this->arResult['BASKET'] = $basket;
    }

    /**
     * @param int $offerId
     *
     * @return Offer|null
     */
    public function getOffer(int $offerId): ?Offer
    {
        if ($offerId <= 0) {
            return null;
        }
        if (!isset($this->offers[$offerId])) {
            $this->offers[$offerId] = OfferQuery::getById($offerId,
                [
                    'ID',
                    'IBLOCK_ID',
                    'PROPERTY_IMG',
                    'PROPERTY_CML2_LIK',
                ]);
        }

        return $this->offers[$offerId];
    }

    /**
     * @param int $offerId
     *
     * @return ResizeImageDecorator|null
     */
    public function getImage(int $offerId): ?ResizeImageDecorator
    {
        if ($offerId <= 0) {
            return null;
        }
        if (!isset($this->images[$offerId])) {
            $offer = $this->getOffer($offerId);
            $image = null;
            if ($offer !== null) {
                $images = $offer->getResizeImages(110, 110);
                $this->images[$offerId] = $images->first();
            }
        }

        return $this->images[$offerId];
    }

    /**
     *
     *
     * @param Basket $basket
     *
     * @return int
     */
    public function getBasketCountWithoutGifts(Basket $basket): int
    {
        return $this->getBasketItemsWithoutGifts($basket)->count();
    }

    /**
     * @param Basket $basket
     *
     * @return BasketBase
     */
    public function getBasketItemsWithoutGifts(Basket $basket): BasketBase
    {
        if ($this->basketItemsWithoutGifts === null) {
            $this->basketItemsWithoutGifts = Basket::create($basket->getSiteId());
            /** @var BasketItem $basketItem */
            foreach ($basket->getBasketItems() as $basketItem) {
                if ($this->basketService->isBasketPropEmpty($basketItem->getId(), 'IS_GIFT')) {
                    $this->basketItemsWithoutGifts->addItem($basketItem);
                }
            }
        }

        return $this->basketItemsWithoutGifts;
    }
}
