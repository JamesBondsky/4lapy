<?php

namespace FourPaws\Components;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketItem;
use CBitrixComponent;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Variant;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\EcommerceBundle\Service\RetailRocketService;
use FourPaws\External\Manzana\Dto\ExtendedAttribute;
use FourPaws\External\Manzana\Exception\ExecuteErrorException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\SaleBundle\Service\BasketService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection
 *
 * Class CatalogElementSnippet
 */
class CatalogElementSnippet extends CBitrixComponent
{
    /**
     * @var GoogleEcommerceService
     */
    private $ecommerceService;
    /**
     * @var RetailRocketService
     */
    private $retailRocketService;
    /**
     * @var BasketService
     */
    private $basketService;
    /**
     * @var StampService
     */
    private $stampService;

    /**
     * CatalogElementSnippet constructor.
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     *
     * @param CBitrixComponent $component
     */
    public function __construct($component)
    {
        $container = Application::getInstance()->getContainer();
        $this->ecommerceService = $container->get(GoogleEcommerceService::class);
        $this->retailRocketService = $container->get(RetailRocketService::class);
        $this->basketService = $container->get(BasketService::class);
        $this->stampService = $container->get(StampService::class);

        parent::__construct($component);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['PRODUCT'] = $params['PRODUCT'] instanceof Product ? $params['PRODUCT'] : null;
        $params['CATEGORY'] = $params['CURRENT_CATEGORY'] instanceof Category ? $params['CURRENT_CATEGORY'] : null;

        $params['NOT_CATALOG_ITEM_CLASS'] = $params['NOT_CATALOG_ITEM_CLASS'] ?? 'N';

        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 360000;
        $params['CACHE_TYPE'] = $params['CACHE_TIME'] === 0 ? 'N' : $params['CACHE_TYPE'];

        $params['OFFER_FILTER'] = $params['OFFER_FILTER'] ?? [];
        $params['SHARE_ID'] = $params['SHARE_ID'] ?? 0;
        $params['SHARE_ID'] = (int)$params['SHARE_ID'];

        $params['GOOGLE_ECOMMERCE_TYPE'] = (string)$params['GOOGLE_ECOMMERCE_TYPE'] ?: 'Каталог';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @return void
     *
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ExecuteErrorException
     * @throws ExecuteException
     * @throws LoaderException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    public function executeComponent(): void
    {
        if ($this->startResultCache()) {
            parent::executeComponent();

            if ($this->arParams['PRODUCT']) {
                /**
                 * @var Product  $product
                 * @var Category $category
                 */
                $this->arResult['CATEGORY'] = $category = $this->arParams['CATEGORY'];
                $this->arResult['PRODUCT'] = $product = $this->arParams['PRODUCT'];
                $this->arResult['CURRENT_OFFER'] = $currentOffer = $this->getCurrentOffer($product);

                if (StampService::IS_STAMPS_OFFER_ACTIVE && $currentOffer) {
//                    $this->arResult['EXCHANGE_RULE'] = $this->getExchangeRule($currentOffer); марки с учетом корзины
                    $stampLevels = [];
                    $maxCanUse = 0;

                    $exchangeRules = StampService::EXCHANGE_RULES[$currentOffer->getXmlId()] ?? false;

                    if (!$exchangeRules) {
                        $exchangeRules = [];
                    }

                    try {
                        $activeStampsCount = $this->stampService->getActiveStampsCount();
                    } catch (\Exception $e) {
                        $activeStampsCount = 0;
                    }
                    foreach ($exchangeRules as $exchangeRule) {
                        if (($exchangeRule['stamps'] <= $activeStampsCount) && ($exchangeRule['stamps'] > $maxCanUse)) {
                            $maxCanUse = $exchangeRule['stamps'];
                        }
                    }

                    foreach ($exchangeRules as $exchangeRule) {
                        $stampLevels[] = [
                            'price' => $exchangeRule['price'],
                            'stamps' => $exchangeRule['stamps'],
                            'is_max_level' => ($exchangeRule['stamps'] == $maxCanUse)
                        ];
                    }

                    $this->arResult['EXCHANGE_RULE'] = $stampLevels;
                }

                if ($category && $product->getIblockSectionId() !== $category->getId()) {
                    $product->withDetailPageUrl(
                        \sprintf(
                            '%s%s.html',
                            $category->getSectionPageUrl(),
                            $product->getCode()
                        )
                    );
                }

                if (!$currentOffer) {
                    $this->abortResultCache();

                    return;
                }

                TaggedCacheHelper::addManagedCacheTags([
                    'iblock:item:' . $currentOffer->getId(),
                    'iblock:item:' . $product->getId(),
                ]);

                $this->includeComponentTemplate();

                return;
            }

            $this->abortResultCache();
        }
    }

    /**
     * @param Product $product
     *
     * @return mixed|null
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    protected function getCurrentOffer(Product $product)
    {
        if (!empty($this->arParams['CURRENT_OFFER']) && $this->arParams['CURRENT_OFFER'] instanceof Offer) {
            $currentOffer = $this->arParams['CURRENT_OFFER'];
        } else {
            $product->getOffers(true, $this->arParams['OFFER_FILTER']);
            $offers = $product->getOffersSorted();
            /** @var Offer $offer */
            $currentOffer = $offers->first();
            foreach ($offers as $offer) {
                $offer->setProduct($product);

                if ($offer->getImagesIds()) {
                    $currentOffer = $offer;
                    break;
                }
            }

            // заранее выбранный оффер при фильтре размера
            /** @var Category $category */
            $category = $this->arParams['CURRENT_CATEGORY'];
            if($category){
                $sizeFilter = $category->getFilters()->getSizeFilter();
                if($sizeFilter && !$sizeFilter->getCheckedVariants()->isEmpty()){
                    $arSizeFilter = [];

                    /** @var Variant $variant */
                    foreach ($sizeFilter->getCheckedVariants() as $variant){
                        $arSizeFilter[] = $variant->getValue();
                    }
                    /** @var Offer $offer */
                    foreach ($offers as $offer) {
                        if(in_array($offer->getClothingSize()->getXmlId(), $arSizeFilter)){
                            $currentOffer = $offer;
                            break;
                        }
                    }
                }
            }
        }

        return $currentOffer;
    }

    /**
     * @param Offer $currentOffer
     * @return array
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ExecuteErrorException
     * @throws ExecuteException
     */
    protected function getExchangeRule($currentOffer)
    {
        $stampLevels = [];
        $maxStampsLevelValue = false;

        $exchangeRule = StampService::EXCHANGE_RULES[$currentOffer->getXmlId()] ?? false;

        if (!$exchangeRule) {
            return [];
        }

        // ищем товар в корзизе
        /** @var BasketItem $basketItem */
        foreach ($this->basketService->getBasket()->getBasketItems() as $basketItem) {
            if ($basketItem->getProductId() == $currentOffer->getId()) {
                if (isset($basketItem->getPropertyCollection()->getPropertyValues()['MAX_STAMPS_LEVEL'])) {
                    $maxStampsLevelValue = unserialize($basketItem->getPropertyCollection()->getPropertyValues()['MAX_STAMPS_LEVEL']['VALUE']);
                }
            }
        }

        // если товар не нашли, то считаем сколько марок пользователь может потратить на один товар
        if (!$maxStampsLevelValue) {
            $extendedAttributeCollection = new ArrayCollection();
            foreach ($exchangeRule as $stampRule) {
                $extendedAttributeCollection->add(
                    (new ExtendedAttribute())->setKey($stampRule['title'])->setValue(1)
                );
            }

            $maxStampsLevelValue = $this->stampService->getMaxAvailableLevel($extendedAttributeCollection, $this->stampService->getActiveStampsCount());
        }

        foreach ($exchangeRule as $rule) {
            if ($rule['title'] === $maxStampsLevelValue['key']) {
                $rule['is_max_level'] = true;
            } else {
                $rule['is_max_level'] = false;
            }

            $stampLevels[] = $rule;
        }

        return $stampLevels;
    }

    /**
     * @return GoogleEcommerceService
     */
    public function getEcommerceService(): GoogleEcommerceService
    {
        return $this->ecommerceService;
    }

    /**
     * @return RetailRocketService
     */
    public function getRetailRocketService(): RetailRocketService
    {
        return $this->retailRocketService;
    }
}
