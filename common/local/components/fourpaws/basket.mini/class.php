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

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\SaleBundle\Exception\BasketUserInitializeException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
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
    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var BasketUserService
     */
    protected $basketUserService;

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
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws \Exception
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     */
    public function prepareResult(): void
    {
        /** @var Basket $basket */
        $basket = $this->basketService->getBasket(false, $this->arParams['FUSER_ID']);

        $basketItems = [];
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $basketItems[] = [
                'ID'              => $basketItem->getId(),
                'NAME'            => $basketItem->getField('NAME'),
                'WEIGHT'          => $basketItem->getWeight(),
                'PRODUCT_ID'      => $basketItem->getProductId(),
                'PRICE'           => $basketItem->getPrice(),
                'BASKE_PRICE'     => $basketItem->getBasePrice(),
                'QUANTITY'        => $basketItem->getQuantity(),
                'IS_GIFT'         => $basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT']['VALUE']
                    ? BitrixUtils::BX_BOOL_TRUE
                    : BitrixUtils::BX_BOOL_FALSE,
                'DETAIL_PAGE_URL' => $basketItem->getField('DETAIL_PAGE_URL'),
            ];
        }

        TaggedCacheHelper::addManagedCacheTag('basket:' . $basket->getFUserId());
        $this->arResult = [
            'BASKET' => $basketItems,
        ];
    }
}
