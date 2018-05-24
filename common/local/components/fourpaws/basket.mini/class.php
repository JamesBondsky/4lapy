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
use FourPaws\App\Application;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\SaleBundle\Service\BasketService;

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

    /** @var array */
    public $offers;

    /** @var array $images */
    private $images;


    public function onPrepareComponentParams($params): array
    {
        /** отключаем кеширваоние */
        $params['CACHE_TIME'] = 0;
        $params['CACHE_TYPE'] = 'N';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * Prepare component result
     */
    public function prepareResult(): void
    {
        if (!$this->loadServices()) {
            $this->abortResultCache();
            return;
        }
        /** @var Basket $basket */
        $basket = $this->arParams['BASKET'];
        if (null === $basket || !\is_object($basket) || !($basket instanceof Basket)) {
            $basket = $this->basketService->getBasket();
        }

        $this->arResult['BASKET'] = $basket;
    }

    /**
     * @param int $offerId
     *
     * @return Offer|null
     */
    public function getOffer(int $offerId): ?Offer
    {
        if($offerId <= 0){
            return null;
        }
        if (!isset($this->offers[$offerId])) {
            $this->offers[$offerId] = OfferQuery::getById($offerId);
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
        if($offerId <= 0){
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
     * @return bool
     */
    private function loadServices(): bool
    {
        try {
            $container = Application::getInstance()->getContainer();
            $this->basketService = $container->get(BasketService::class);
        } catch (\Exception $e) {
            $this->log()->error('ошибка загрузки сервиса: ' . $e->getMessage());
            return false;
        }
        return true;
    }
}