<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Model\BundleItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterHelper;
use FourPaws\CatalogBundle\Service\SortService;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\DateHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Exception\CategoryNotFoundException;
use FourPaws\MobileApiBundle\Exception\NotFoundProductException;
use FourPaws\Search\Model\Navigation;
use FourPaws\Search\SearchService;
use FourPaws\StoreBundle\Service\StockService;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\BundleItem as BundleItemOffer;
use Symfony\Component\HttpFoundation\Request;


class ProductService
{
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var LocationService
     */
    private $locationService;
    /**
     * @var DeliveryService
     */
    private $deliveryService;
    /**
     * @var CategoriesService
     */
    private $categoriesService;
    /**
     * @var FilterHelper
     */
    private $filterHelper;
    /**
     * @var SearchService
     */
    private $searchService;
    /**
     * @var SortService
     */
    private $sortService;

    public function __construct(
        CategoriesService $categoriesService,
        UserService $userService,
        LocationService $locationService,
        DeliveryService $deliveryService,
        FilterHelper $filterHelper,
        SortService $sortService,
        SearchService $searchService
    )
    {
        $this->categoriesService = $categoriesService;
        $this->userService = $userService;
        $this->locationService = $locationService;
        $this->deliveryService = $deliveryService;
        $this->filterHelper = $filterHelper;
        $this->sortService = $sortService;
        $this->searchService = $searchService;
    }

    /**
     * @param Request $request
     * @param int $categoryId
     * @param string $sort
     * @param int $count
     * @param int $page
     * @param string $searchQuery
     * @return ArrayCollection
     * @throws CategoryNotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public function getList(
        Request $request,
        int $categoryId = 0,
        string $sort = 'popular',
        int $count = 10,
        int $page = 1,
        string $searchQuery = ''
    ): ArrayCollection
    {
        $filters = new FilterCollection();
        if ($categoryId > 0) {
            $category = $this->categoriesService->getById($categoryId);
            $this->filterHelper->initCategoryFilters($category, $request);
            $filters = $category->getFilters();
        }

        $sort = $this->sortService->getSorts($sort, strlen($searchQuery) > 0)->getSelected();

        $nav = (new Navigation())
            ->withPage($page)
            ->withPageSize($count);

        $productSearchResult = $this->searchService->searchProducts($filters, $sort, $nav, $searchQuery);
        /** @var ProductCollection $productCollection */
        $productCollection = $productSearchResult->getProductCollection();

        return (new ArrayCollection([
            'products' => $productCollection->map(\Closure::fromCallable([$this, 'mapProductForList']))->getValues(),
            'cdbResult' => $productCollection->getCdbResult()
        ]));
    }

    /**
     * @param Product $product
     * @return FullProduct
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    protected function mapProductForList(Product $product): FullProduct
    {
        /** @var Offer $currentOffer */
        $currentOffer = $this->getCurrentOfferForList($product);
        return $this->convertToFullProduct($product, $currentOffer);
    }

    /**
     * @param Product $product
     *
     * @param array $offerFilter
     * @return mixed|null
     */
    protected function getCurrentOfferForList(Product $product, $offerFilter = [])
    {
        $product->getOffers(true, $offerFilter);
        $offers = $product->getOffersSorted();
        $foundOfferWithImages = false;
        $currentOffer = $offers->last();
        foreach ($offers as $offer) {
            $offer->setProduct($product);

            if (!$foundOfferWithImages || $offer->getImagesIds()) {
                $currentOffer = $offer;
            }
        }

        // костыль потому что в allStocks вместо объекта StockCollection приходит просто массив с кодами магазинов...
        // взято из метода FourPaws\Catalog\Model\getAllStocks()
        $stockService = Application::getInstance()->getContainer()->get(StockService::class);
        $currentOffer->withAllStocks($stockService->getStocksByOffer($currentOffer));
        // end костыль

        return $currentOffer;
    }

    /**
     * Возвращает продукт для карточки товара
     * @param int $id
     * @return FullProduct
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getOne(int $id): FullProduct
    {
        $offer = (new OfferQuery())->getById($id);
        if (!$offer) {
            throw new NotFoundProductException("Предложение с ID $id не найдено");
        }
        $product = $offer->getProduct();

        $fullProduct = $this->convertToFullProduct($product, $offer);
        $fullProduct
            ->setNutritionRecommendations($product->getNormsOfUse()->getText())
            ->setNutritionFacts($product->getComposition()->getText())
            ->setPackingVariants($this->getPackingVariants($product))   // фасовки
            ->setSpecialOffer($this->getSpecialOffer($offer))           // акция
            ->setFlavours($this->getFlavours($offer))                   // вкус
            ->setAvailability($offer->getAvailabilityText())            // товар под заказ
            ->setDelivery($this->getDeliveryText($offer))               // товар под заказ
            ->setPickup($this->getPickupText($offer))                   // товар под заказ
            // ->setCrossSale($this->getCrossSale($offer))              // похожие товары
            ->setBundle($this->getBundle($offer))                       // с этим товаром покупают
            ;

        return $fullProduct;
    }

    /**
     * @param Offer $offer
     * @return array<ShortProduct\Tag()>
     * @throws \Bitrix\Main\SystemException
     */
    public function getTags(Offer $offer)
    {
        $tags = [];
        if ($offer->isHit()) {
            $tags[] = (new ShortProduct\Tag())->setImg(MarkHelper::MARK_HIT_IMAGE_SRC);
        }
        if ($offer->isNew()) {
            $tags[] = (new ShortProduct\Tag())->setImg(MarkHelper::MARK_NEW_IMAGE_SRC);
        }
        if ($offer->isSale()) {
            $tags[] = (new ShortProduct\Tag())->setImg(MarkHelper::MARK_SALE_IMAGE_SRC);
        }
        return $tags;
    }

    /**
     * @param Product $product
     * @param Offer $offer
     * @param int $quantity
     * @return ShortProduct
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function convertToShortProduct(Product $product, Offer $offer, $quantity = 1): ShortProduct
    {
        $shortProduct = (new ShortProduct())
            ->setId($offer->getId())
            ->setTitle($offer->getName())
            ->setXmlId($offer->getXmlId())
            ->setBrandName($product->getBrandName())
            ->setWebPage($offer->getCanonicalPageUrl())
            ->setPicture($offer->getImages() ? $offer->getImages()->first() : '')
            ->setPicturePreview($offer->getResizeImages(200, 250) ? $offer->getResizeImages(200, 250)->first() : '')
            ->setIsByRequest($offer->isByRequest());

        // цена
        $price = (new Price())
            ->setActual($offer->getOldPrice())
            ->setOld($offer->getPrice());
        $shortProduct->setPrice($price);

        // лейблы
        $shortProduct->setTag($this->getTags($offer));

        // бонусы
        $shortProduct->setBonusAll($offer->getBonusCount(3, $quantity));
        $shortProduct->setBonusUser($offer->getBonusCount($this->userService->getDiscount(), $quantity));

        return $shortProduct;
    }

    /**
     * @param Product $product
     * @param Offer $offer
     * @return FullProduct
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function convertToFullProduct(Product $product, Offer $offer): FullProduct
    {
        $shortProduct = $this->convertToShortProduct($product, $offer);
        $fullProduct = (new FullProduct())
            ->setDetailsHtml($product->getDetailText()->getText());;

        // toDo: is there any better way to merge ShortProduct into FullProduct?
        $fullProduct
            ->setId($shortProduct->getId())
            ->setTitle($shortProduct->getTitle())
            ->setXmlId($shortProduct->getXmlId())
            ->setBrandName($shortProduct->getBrandName())
            ->setWebPage($shortProduct->getWebPage())
            ->setPicture($shortProduct->getPicture())
            ->setPicturePreview($shortProduct->getPicturePreview())
            ->setPrice($shortProduct->getPrice())
            ->setTag($shortProduct->getTag())
            ->setBonusAll($shortProduct->getBonusAll())
            ->setBonusUser($shortProduct->getBonusUser())
            ->setIsByRequest($shortProduct->getIsByRequest());

        return $fullProduct;
    }

    /**
     * Возможные доставки
     * @param Offer $offer
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getDeliveries(Offer $offer)
    {
        $userLocationCode = $this->userService->getSelectedCity()['CODE'];

        /** @var CalculationResultInterface[] $deliveries */
        $allDeliveryCodes = array_merge(DeliveryService::DELIVERY_CODES, DeliveryService::PICKUP_CODES);
        $deliveries = $this->deliveryService->getByLocation($userLocationCode, $allDeliveryCodes);
        $deliveriesWithStock = [];
        foreach ($deliveries as $delivery) {
            $delivery->setStockResult(
                $this->deliveryService->getStockResultForOffer($offer, $delivery)
            )->setCurrentDate(new \DateTime());
            if ($delivery->isSuccess()) {
                $deliveriesWithStock[] = $delivery;
            }
        }
        return $deliveriesWithStock;
    }

    /**
     * Отформатированный текст о доставке
     * @param Offer $offer
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getDeliveryText(Offer $offer): string
    {
        /** @var $deliveryResult DeliveryResult */
        $deliveryResult = $this->filterDeliveries($this->getDeliveries($offer));
        if (!$deliveryResult) {
            return '';
        }
        return $deliveryResult->getTextForOffer($offer->isByRequest(), true);
    }

    /**
     * Отформатированный текст о самовывозе
     * @param Offer $offer
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getPickupText(Offer $offer): string
    {
        /** @var $pickupResult PickupResult */
        $pickupResult = $this->filterPickups($this->getDeliveries($offer));
        return $pickupResult->getTextForOffer();
    }

    /**
     * Фасовки товара
     * @param Product $product
     * @return FullProduct\PackingVariant[]
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getPackingVariants(Product $product): array
    {
        $offers = $product->getOffersSorted();
        if (empty($offers)) {
            return [];
        }

        $packingVariants = [];
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            $packingVariants[] = (new FullProduct\PackingVariant())
                ->setPrice($offer->getPrice())
                ->setOfferId($offer->getId())
                ->setWeight($offer->getPackageLabel(false, 0))
                ->setHasSpecialOffer($offer->hasAction());
        }
        return $packingVariants;
    }

    /**
     * Акция товара
     * @param Offer $offer
     * @return FullProduct\SpecialOffer
     * @throws \Bitrix\Main\SystemException
     */
    public function getSpecialOffer(Offer $offer): FullProduct\SpecialOffer
    {
        /** @var Share $specialOfferModel */
        $specialOfferModel = $offer->getShare()->current();
        $specialOffer = (new FullProduct\SpecialOffer());
        if ($specialOfferModel) {
            $specialOffer
                ->setId($specialOfferModel->getId())
                ->setName($specialOfferModel->getName())
                ->setDescription($specialOfferModel->getPreviewText());

            if ($specialOfferModel->getDateActiveFrom() && $specialOfferModel->getDateActiveTo()) {
                $dateFrom = DateHelper::replaceRuMonth($specialOfferModel->getDateActiveFrom()->format('d #n# Y'), DateHelper::GENITIVE);
                $dateTo = DateHelper::replaceRuMonth($specialOfferModel->getDateActiveTo()->format('d #n# Y'), DateHelper::GENITIVE);
                $specialOffer->setDate($dateFrom . " - " . $dateTo);
            }
            if ($specialOfferModel->hasLabelImage()) {
                $specialOffer->setImage($specialOfferModel->getPropertyLabelImageFileSrc());
            }
        }
        return $specialOffer;
    }

    /**
     * Вкусы товара
     * @param Offer $offer
     * @return FullProduct\Flavour[]
     */
    public function getFlavours(Offer $offer): array
    {
        if (!empty($offer->getFlavourCombination())) {
            $unionOffers = $this->getOffersByUnion('flavour', $offer->getFlavourCombination());
            if (!$unionOffers->isEmpty()) {
                $unionOffersSorted = [];
                foreach ($unionOffers as $unionOffer) {
                    $unionOffersSorted[$unionOffer->getFlavourWithWeight()] = $unionOffer;
                }
                ksort($unionOffersSorted);
                $flavours = [];
                foreach ($unionOffersSorted as $flavourWithWeight => $unionOffer) {
                    $flavours[] = (new FullProduct\Flavour())
                        ->setOfferId($unionOffer->getId())
                        ->setTitle($flavourWithWeight);
                }
                return $flavours;
            }
        }
        return [];
    }

    /**
     * Похожие товары
     * @param Offer $offer
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getCrossSale(Offer $offer)
    {
        if ($bundle = $offer->getBundle()) {
            $crossSale = [];
            foreach ($bundle->getProducts() as $bundleItem) {
                $bundleItemOffer = $bundleItem->getOffer();
                $crossSale[] = (new FullProduct\CrossSale())
                    ->setOfferId($bundleItemOffer->getId())
                    ->setTitle($bundleItemOffer->getName())
                    ->setPrice($bundleItemOffer->getPrice())
                    ->setImage($bundleItemOffer->getImages()->current())
                ;
            }
            return $crossSale;
        }
        return [];
    }

    /**
     * С этим товаром часто берут
     * @param Offer $offer
     * @return FullProduct\Bundle
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getBundle(Offer $offer)
    {
        $bundleItems = [];
        $oldTotalPrice = 0;
        $totalPrice = 0;
        $bonusAmount = 0;
        if ($bundle = $offer->getBundle()) {
            $percent = $this->userService->getCurrentUserBonusPercent();
            /** @var BundleItem $bundleItem */
            foreach ($bundle->getProducts() as $bundleItem) {
                $bundleItemOffer = $bundleItem->getOffer();
                $bundleItems[] = (new BundleItemOffer())
                    ->setOfferId($bundleItemOffer->getId())
                    ->setTitle($bundleItemOffer->getName())
                    ->setPrice(
                        (new Price)
                            ->setActual($bundleItemOffer->getPrice())
                            ->setOld($bundleItemOffer->getOldPrice())
                    )
                    ->setImage($bundleItemOffer->getImages()->current())
                    ->setQuantity($bundleItemOffer->getQuantity())
                    ->setWeight($bundleItemOffer->getCatalogProduct()->getWeight());

                $totalPrice += $bundleItemOffer->getCatalogPrice() * $bundleItem->getQuantity();
                $oldTotalPrice += $bundleItemOffer->getCatalogOldPrice() * $bundleItem->getQuantity();
                $bonusAmount += $bundleItemOffer->getBonusCount($percent, $bundleItem->getQuantity());
            }
        }
        return (new FullProduct\Bundle())
            ->setBundleItems($bundleItems)
            ->setTotalPrice(
                (new Price)
                    ->setActual($totalPrice)
                    ->setOld($oldTotalPrice)
            )
            ->setBonusAmount($bonusAmount);
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     *
     * @return null|DeliveryResultInterface
     */
    protected function filterDeliveries($deliveries): ?DeliveryResultInterface
    {
        $filtered = array_filter(
            $deliveries,
            function (CalculationResultInterface $delivery) {
                return $this->deliveryService->isDelivery($delivery);
            }
        );

        return reset($filtered) ?: null;
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     *
     * @return null|PickupResultInterface
     */
    protected function filterPickups($deliveries): ?PickupResultInterface
    {
        $filtered = array_filter(
            $deliveries,
            function (CalculationResultInterface $delivery) {
                return $this->deliveryService->isPickup($delivery);
            }
        );

        return reset($filtered) ?: null;
    }

    /**
     * @param string $type
     * @param string $val
     *
     * @return OfferCollection
     */
    protected function getOffersByUnion(string $type, string $val): OfferCollection
    {
        $unionOffers = [];
        $offerCollection = null;
        switch ($type) {
            case 'color':
                $offerCollection = (new OfferQuery())->withFilter(['PROPERTY_COLOUR_COMBINATION' => $val])
                    ->exec();
                break;
            case 'flavour':
                $offerCollection = (new OfferQuery())->withFilter(['PROPERTY_FLAVOUR_COMBINATION' => $val])
                    ->exec();
                break;
        }
        if (null !== $offerCollection) {
            $unionOffers[$type][$val] = $offerCollection;
        }


        return $unionOffers[$type][$val];
    }

}
