<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\BitrixOrm\Model\Image;
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
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\ImageHelper;
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
use Symfony\Component\HttpFoundation\Request;


class ProductService
{
    const LIST_IMAGE_WIDTH = 200;
    const LIST_IMAGE_HEIGHT = 250;

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
     * Список товаров в каталоге
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
     * Мэппинг полей товара для списка
     * @param Product $product
     * @return FullProduct
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \ImagickException
     */
    protected function mapProductForList(Product $product): FullProduct
    {
        /** @var Offer $currentOffer */
        $currentOffer = $this->getCurrentOfferForList($product);
        $fullProduct = $this->convertToFullProduct($product, $currentOffer);
        // товары всегда доступны в каталоге (недоступные просто не должны быть в выдаче)
        $fullProduct->setIsAvailable(true);
        return $fullProduct;
    }

    /**
     * Текущее ТП для товара в списке
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
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            $offer->setProduct($product);

            if (!$foundOfferWithImages || $offer->getImagesIds()) {
                $currentOffer = $offer;
            }
        }

        // toDo рефакторинг
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
     * @throws \ImagickException
     */
    public function getOne(int $id): FullProduct
    {
        $offer = (new OfferQuery())->getById($id);
        if (!$offer) {
            throw new NotFoundProductException("Предложение с ID $id не найдено");
        }
        $product = $offer->getProduct();

        $fullProduct = $this->convertToFullProduct($product, $offer);
        $fullProduct->setIsAvailable($offer->isAvailable()); // returns ShortProduct
        $fullProduct
            ->setSpecialOffer($this->getSpecialOffer($offer))           // акция
            ->setFlavours($this->getFlavours($offer))                   // вкус
            ->setAvailability($offer->getAvailabilityText())            // товар под заказ
            ->setDelivery($this->getDeliveryText($offer))               // товар под заказ
            ->setPickup($this->getPickupText($offer))                   // товар под заказ
            // ->setCrossSale($this->getCrossSale($offer))              // похожие товары
            ->setBundle($this->getBundle($offer))                       // с этим товаром покупают
            ->setPictureList($this->getPictureList($product))
            ;

        if ($product->getNormsOfUse()->getText() || $product->getLayoutRecommendations()->getText()) {
            if ($product->getLayoutRecommendations()->getText() != '' && $product->getLayoutRecommendations()->getText() != null) {
                $fullProduct->setNutritionRecommendations($product->getLayoutRecommendations()->getText());
            } else {
                $fullProduct->setNutritionRecommendations($product->getNormsOfUse()->getText());
            }
        }

        if ($product->getComposition()->getText() || $product->getLayoutComposition()->getText()) {
            if ($product->getLayoutComposition()->getText() != '' && $product->getLayoutComposition()->getText() != null) {
                $fullProduct->setNutritionFacts($product->getLayoutComposition()->getText());
            } else {
                $fullProduct->setNutritionFacts($product->getComposition()->getText());
            }
        }

        return $fullProduct;
    }

    public function getMyProducts()
    {

    }

    /**
     * @param Offer $offer
     * @return array<ShortProduct\Tag()>
     * @throws \Bitrix\Main\SystemException
     * @throws \ImagickException
     */
    public function getTags(Offer $offer)
    {
        $tags = [];
        if (
            ($offer->isHit() && $tag = $this->getTag(MarkHelper::MARK_HIT_IMAGE_SRC))
            || ($offer->isNew() && $tag = $this->getTag(MarkHelper::MARK_NEW_IMAGE_SRC))
            || ($offer->isSale() && $tag = $this->getTag(MarkHelper::MARK_SALE_IMAGE_SRC))
        ) {
            $tags[] = $tag;
        }
        return $tags;
    }

    /**
     * @param string $svg
     * @return ShortProduct\Tag|false
     * @throws \Bitrix\Main\SystemException
     * @throws \ImagickException
     */
    public function getTag(string $svg)
    {
        try {
            $png = ImageHelper::convertSvgToPng($svg);
        } catch (NotFoundException $e) {
            return false;
        }
        return (new ShortProduct\Tag())->setImg($png);
    }

    /**
     * @param Product $product
     * @param Offer $offer
     * @param int $quantity
     * @return ShortProduct
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \ImagickException
     */
    public function convertToShortProduct(Product $product, Offer $offer, $quantity = 1): ShortProduct
    {
        $shortProduct = (new ShortProduct())
            ->setId($offer->getId())
            ->setTitle($offer->getName())
            ->setXmlId($offer->getXmlId())
            ->setBrandName($product->getBrandName())
            ->setWebPage($offer->getCanonicalPageUrl())
            ->setIsByRequest($offer->isByRequest())
            ->setIsAvailable($offer->isAvailable());

        // большая картинка
        if ($images = $offer->getImages()) {
            /** @var Image $picture */
            $picture = $images->first();
            $pictureSrc = \CFile::getPath($picture->getId());
            $pictureSrc = (new FullHrefDecorator($pictureSrc))->getFullPublicPath();
            $shortProduct->setPicture($pictureSrc);
        }

        // картинка ресайз (возможно не используется, но это не точно)
        if ($resizeImages = $offer->getResizeImages(static::LIST_IMAGE_WIDTH, static::LIST_IMAGE_HEIGHT)) {
            $shortProduct->setPicturePreview($resizeImages->first());
        }

        // цена
        $price = (new Price())
            ->setActual($offer->getPrice())
            ->setOld($offer->getOldPrice());
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
     * @param bool $needPackingVariants
     * @return FullProduct
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \ImagickException
     */
    public function convertToFullProduct(Product $product, Offer $offer, $needPackingVariants = true): FullProduct
    {
        $shortProduct = $this->convertToShortProduct($product, $offer);
        $detailText = $product->getDetailText()->getText();
        $detailText = ImageHelper::appendDomainToSrc($detailText);
        $fullProduct = (new FullProduct())
            ->setDetailsHtml($detailText)
            ->setWeight($offer->getPackageLabel(false, 0))
            ->setHasSpecialOffer($offer->isShare())
        ;

        if ($needPackingVariants) {
            $fullProduct->setPackingVariants($this->getPackingVariants($product, $offer));   // фасовки
        }

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
            ->setIsByRequest($shortProduct->getIsByRequest())
            ->setIsAvailable($shortProduct->getIsAvailable());

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
        return $deliveryResult->getTextForOffer($offer->getPrice(), $offer->isByRequest(), true);
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
        if ($pickupResult = $this->filterPickups($this->getDeliveries($offer))) {
            return $pickupResult->getTextForOffer(false);
        }
        return '';
    }

    /**
     * Фасовки товара
     * @param Product $product
     * @param Offer $currentOffer
     * @return FullProduct[]
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \ImagickException
     */
    public function getPackingVariants(Product $product, Offer $currentOffer): array
    {
        $offers = $product->getOffersSorted();
        if (empty($offers)) {
            return [];
        }

        $packingVariants = [];
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            if ($offer->getId() === $currentOffer->getId()) {
                continue;
            }
            // toDo рефакторинг
            // костыль потому что в allStocks вместо объекта StockCollection приходит просто массив с кодами магазинов...
            // взято из метода FourPaws\Catalog\Model\getAllStocks()
            $stockService = Application::getInstance()->getContainer()->get(StockService::class);
            $offer->withAllStocks($stockService->getStocksByOffer($offer));
            // end костыль

            $packingVariants[] = $this->convertToFullProduct($product, $offer, false);
        }
        return $packingVariants;
    }

    /**
     * Акция товара
     * @param Offer $offer
     * @return FullProduct\SpecialOffer|null
     * @throws \Bitrix\Main\SystemException
     */
    public function getSpecialOffer(Offer $offer)
    {
        /** @var Share $specialOfferModel */
        $specialOfferModel = $offer->getShare()->current();
        $specialOffer = (new FullProduct\SpecialOffer());
        if (!$specialOfferModel) {
            return null;
        }
        $specialOffer
            ->setId($specialOfferModel->getId())
            ->setName($specialOfferModel->getName())
            ->setDescription($specialOfferModel->getPreviewText())
            ->setImage($specialOfferModel->getPreviewPictureSrc());

        if ($specialOfferModel->getDateActiveFrom() && $specialOfferModel->getDateActiveTo()) {
            $dateFrom = DateHelper::replaceRuMonth($specialOfferModel->getDateActiveFrom()->format('d #n# Y'), DateHelper::GENITIVE);
            $dateTo = DateHelper::replaceRuMonth($specialOfferModel->getDateActiveTo()->format('d #n# Y'), DateHelper::GENITIVE);
            $specialOffer->setDate($dateFrom . " - " . $dateTo);
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
     * @return FullProduct\Bundle:null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \ImagickException
     */
    public function getBundle(Offer $offer)
    {
        $bundleItems = [];
        $oldTotalPrice = 0;
        $totalPrice = 0;
        $bonusAmount = 0;
        if (!$bundle = $offer->getBundle()) {
            return null;
        }
        $percent = $this->userService->getCurrentUserBonusPercent();
        /** @var BundleItem $bundleItem */
        foreach ($bundle->getProducts() as $bundleItem) {
            $bundleItemOffer = $bundleItem->getOffer();
            $bundleItemProduct = $bundleItemOffer->getProduct();

            $product = $this->convertToShortProduct($bundleItemProduct, $bundleItemOffer);

            $bundleItems[] = $product;

            $totalPrice += $bundleItemOffer->getCatalogPrice() * $bundleItem->getQuantity();
            $oldTotalPrice += $bundleItemOffer->getCatalogOldPrice() * $bundleItem->getQuantity();
            $bonusAmount += $bundleItemOffer->getBonusCount($percent, $bundleItem->getQuantity());
        }
        return (new FullProduct\Bundle())
            ->setGoods($bundleItems)
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

    protected function getPictureList(Product $product)
    {
        $offers = $product->getOffersSorted();
        if (empty($offers)) {
            return [];
        }

        $images = [];
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            if ($offerImages = $offer->getImages()) {
                foreach ($offerImages as $image) {
                    $images[] = $image;
                }
            }
        }
        return array_unique($images);
    }

}
