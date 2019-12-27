<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Loader;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\BasketItem;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\BitrixOrm\Model\Image;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\BitrixOrm\Query\ShareQuery;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Model\BundleItem;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\ProductIdFilter;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\CatalogBundle\Controller\CatalogController;
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
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\External\Manzana\Dto\ExtendedAttribute;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\ImageHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct\StampLevel;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct\Tag;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Exception\CategoryNotFoundException;
use FourPaws\MobileApiBundle\Exception\NotFoundProductException;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\SaleBundle\Service\BasketRulesService;
use FourPaws\Search\Helper\IndexHelper;
use FourPaws\Search\Model\Navigation;
use FourPaws\Search\SearchService;
use FourPaws\StoreBundle\Service\StockService;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\SaleBundle\Service\BasketService as AppBasketService;
use FourPaws\Catalog\Table\CommentsTable;
use Bitrix\Main\IO\File;
use WebArch\BitrixCache\BitrixCache;
use Symfony\Component\Cache\Simple\FilesystemCache;

class ProductService
{
    const        LIST_IMAGE_WIDTH      = 200;
    const        LIST_IMAGE_HEIGHT     = 250;
    public const DETAIL_PICTURE_WIDTH  = 2000;
    public const DETAIL_PICTURE_HEIGHT = 2000;

    /** @var UserService */
    private $userService;

    /** @var LocationService */
    private $locationService;

    /** @var DeliveryService */
    private $deliveryService;

    /** @var CategoriesService */
    private $categoriesService;

    /** @var FilterHelper */
    private $filterHelper;

    /** @var SearchService */
    private $searchService;

    /** @var SortService */
    private $sortService;

    /** @var AppBasketService */
    private $appBasketService;

    /** @var BasketRulesService */
    private $basketRulesService;

    /** @var StampService */
    private $stampService;

    /** @var int */
    private $productStars;

    /** @var int */
    private $totalComments;


    /**
     * @var bool
     */
    private $forceAtLeastOnePackingVariant = false;


    public function __construct(
        CategoriesService $categoriesService,
        UserService $userService,
        LocationService $locationService,
        DeliveryService $deliveryService,
        FilterHelper $filterHelper,
        SortService $sortService,
        SearchService $searchService,
        AppBasketService $appBasketService,
        BasketRulesService $basketRulesService,
        StampService $stampService
    ) {
        $this->categoriesService  = $categoriesService;
        $this->userService        = $userService;
        $this->locationService    = $locationService;
        $this->deliveryService    = $deliveryService;
        $this->filterHelper       = $filterHelper;
        $this->sortService        = $sortService;
        $this->searchService      = $searchService;
        $this->appBasketService   = $appBasketService;
        $this->basketRulesService = $basketRulesService;
        $this->stampService       = $stampService;
    }

    /**
     * Список товаров в каталоге
     * @param Request $request
     * @param int     $categoryId
     * @param string  $sort
     * @param int     $count
     * @param int     $page
     * @param string  $searchQuery
     * @return ArrayCollection
     * @throws CategoryNotFoundException
     * @throws IblockNotFoundException
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
        string $searchQuery = '',
        int $stockId = 0
    ): ArrayCollection {
        $filters = new FilterCollection();
        if ($categoryId > 0) {
            $category = $this->categoriesService->getById($categoryId);
            $this->filterHelper->initCategoryFilters($category, $request);
            $filters = $category->getFilters();
        }

        if ($stockId > 0) {
            if (!$this->checkShareAccess($stockId)) {
                return (new ArrayCollection([
                    'products'  => [],
                    'cdbResult' => new \CIBlockResult(),
                ]));
            }

            $searchQuery = $this->getProductXmlIdsByShareId($stockId);

//            $category = new \FourPaws\Catalog\Model\Category();
//            $this->filterHelper->initCategoryFilters($category, $request);
//            $filters = $category->getFilters();
//
//            $filterArr = [];
//            foreach ($filters as $filter) {
//                $filterCode   = $filter->getFilterCode();
//                $requestParam = $request->get($filterCode);
//                if ($requestParam) {
//                    $filterArr[] = $filter;
//                }
//            }

//            $filters = new FilterCollection($filterArr);
        } elseif ($searchQuery) {
            /** @see CatalogController::searchAction */
            $searchQuery = mb_strtolower($searchQuery);
            $searchQuery = IndexHelper::getAlias($searchQuery);
        }

        $sort = $this->sortService->getSorts($sort, strlen($searchQuery) > 0)->getSelected();

        $nav = (new Navigation())
            ->withPage($page)
            ->withPageSize($count);;


        $productSearchResult = $this->searchService->searchProducts($filters, $sort, $nav, $searchQuery);

        /** @var ProductCollection $productCollection */
        $productCollection = $productSearchResult->getProductCollection();

        $callBack = \Closure::fromCallable([$this, 'mapProductForList']);

//        if ($stockId > 0) {
            $cache = new FilesystemCache('', 3600 * 24);
            $cacheArr = [];
            $cacheIgnoreKey = ['token', 'sign', 'PHPSESSID'];
            foreach ($_REQUEST as $key => $value) {
                if (!in_array($key, $cacheIgnoreKey)) {
                    $cacheArr[$key] = $value;
                }
            }

            $cacheArr['searchQuery'] = $searchQuery;

            $cacheKey = md5(json_encode($cacheArr));

            if ($cache->has($cacheKey)) {
                $products = $cache->get($cacheKey);
            } else {
                $products = $productCollection
                    ->map($callBack)
                    ->filter(function ($value) {
                        return !is_null($value);
                    })
                    ->getValues();
                $cache->set($cacheKey, $products);
            }
//        } else {
//            $products = $productCollection
//                ->map($callBack)
//                ->filter(function ($value) {
//                    return !is_null($value);
//                })
//                ->getValues();
//        }

//        $productsCache = (new BitrixCache())
//            ->withId(md5($productCacheKey))
//            ->withTime(864000)
//            ->resultOf(function () use ($productCollection, $callBack) {
////                return $callBack;
//                $products = $productCollection
//                    ->map($callBack)
//                    ->filter(function ($value) {
//                        return !is_null($value);
//                    })
//                    ->getValues();
//
//                return $products;
//            });
//
//        $products = $productsCache ?? [];

        return (new ArrayCollection([
            'products'  => $products,
            'cdbResult' => $productCollection->getCdbResult(),
        ]));
    }

    /**
     * @param int[] $ids
     * @return ArrayCollection
     * @throws IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\Catalog\Exception\CategoryNotFoundException
     */
    public function getListFromIds(array $ids)
    {
        $filters = new FilterCollection();
        $filters->add([
            'ID' => $ids,
        ]);

        $sort = $this->sortService->getSorts('popular')->getSelected();

        $productSearchResult = $this->searchService->searchProducts($filters, $sort, new Navigation());
        /** @var ProductCollection $productCollection */
        $productCollection = $productSearchResult->getProductCollection();

        return (new ArrayCollection([
            'products'  => $productCollection
                ->map(\Closure::fromCallable([$this, 'mapProductForList']))
                ->filter(function ($value) {
                    return !is_null($value);
                })
                ->getValues(),
            'cdbResult' => $productCollection->getCdbResult(),
        ]));
    }

    /**
     * @param int[]     $ids
     * @param bool|null $onlyPackingVariants
     * @return ArrayCollection
     * @throws ApplicationCreateException
     * @throws ArgumentException
     */
    public function getListFromXmlIds(array $ids, ?bool $onlyPackingVariants = false): ArrayCollection
    {
        $filters = new FilterCollection();
        //        $filters->add([
        //            'ID' => $ids
        //        ]);

        $sort = $this->sortService->getSorts('popular')->getSelected();

        $productSearchResult = $this->searchService->searchProducts($filters, $sort, new Navigation(), $ids);
        /** @var ProductCollection $productCollection */
        $productCollection = $productSearchResult->getProductCollection();

        $this->forceAtLeastOnePackingVariant = true;
        $productList                         = $productCollection
            ->map(\Closure::fromCallable([$this, 'mapProductForList']))
            ->map(static function (FullProduct $product) use ($ids, $onlyPackingVariants) {
                $packingVariants = $product->getPackingVariants();
                $packingVariants = array_filter($packingVariants, static function (FullProduct $product) use ($ids) {
                    return in_array($product->getXmlId(), $ids, false);
                });
                if ($onlyPackingVariants) {
                    return array_values($packingVariants);
                } else {
                    $product->setPackingVariants($packingVariants);

                    return $product;
                }
            })
            ->filter(static function ($value) {
                return !is_null($value);
            })
            ->getValues();
        $this->forceAtLeastOnePackingVariant = false;

        if ($onlyPackingVariants) {
            $productList = array_reduce($productList, static function ($carry, $productArray) {
                foreach ($productArray as $product) {
                    $carry[] = $product;
                }

                return $carry;
            }, []);
        }

        return new ArrayCollection([
            $productList,
        ]);
    }

    /**
     * Мэппинг полей товара для списка
     * @param Product $product
     * @return FullProduct|null
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws SystemException
     * @throws IblockNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function mapProductForList(Product $product)
    {
        /** @var Offer $currentOffer */
        $currentOffer = $this->getCurrentOfferForList($product);
        if (!$currentOffer) {
            return null;
        }

        foreach ($product->getOffers() as $itemOffer) {
            $itemOffer->setColor();
        }

        $hasColours = (bool)$this->getColours($currentOffer)
            && (bool)$currentOffer->getColor();

        $clothingSizes = [];
        foreach ($product->getOffers() as $itemOffer) {
            $clothingSize = $itemOffer->getClothingSize();
            if ($clothingSize) {
                $clothingSizes[] = $clothingSize->getId();
            }
        }
        $hasOnlyColourCombinations = false;
        if ($hasColours && count($clothingSizes) <= 1) { // есть деление по цветам, но нет деления по размерам
            $hasOnlyColourCombinations = true;
        }


        $fullProduct = $this->convertToFullProduct($product, $currentOffer, true, $this->forceAtLeastOnePackingVariant, $hasOnlyColourCombinations);

        // товары всегда доступны в каталоге (недоступные просто не должны быть в выдаче)
        $fullProduct->setIsAvailable(true);
        return $fullProduct;
    }

    /**
     * Текущее ТП для товара в списке
     * @param Product $product
     *
     * @param array   $offerFilter
     * @return mixed|null
     */
    protected function getCurrentOfferForList(Product $product, $offerFilter = [])
    {
        $product->getOffers(true, $offerFilter);
        $offers               = $product->getOffersSorted();
        $foundOfferWithImages = false;
        $currentOffer         = $offers->last();
        if (!$currentOffer) {
            return null;
        }
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
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \Bitrix\Main\SystemException
     */
    public function getOne(int $id): FullProduct
    {
        $offer = (new OfferQuery())->getById($id);
        if (!$offer) {
            throw new NotFoundProductException();
        }
        $product    = $offer->getProduct();
        $colours    = $this->getColours($offer);
        $hasColours = (bool)$colours;

        $clothingSizes = [];
        foreach ($product->getOffers() as $itemOffer) {
            $clothingSize = $itemOffer->getClothingSize();
            if ($clothingSize) {
                $clothingSizes[] = $clothingSize->getId();
            }
        }
        $hasOnlyColourCombinations = false;
        if ($hasColours && count($clothingSizes) <= 1) { // есть деление по цветам, но нет деления по размерам
            $hasOnlyColourCombinations = true;
        }

        $fullProduct = $this->convertToFullProduct($product, $offer, true, true, $hasOnlyColourCombinations, true);

        $fullProduct->setIsAvailable($offer->isAvailable()); // returns ShortProduct
        $fullProduct
            ->setSpecialOffer($this->getSpecialOffer($offer))           // акция
            ->setFlavours($this->getFlavours($offer))                   // вкус
            ->setColours($colours)                                      // цвет
            ->setAvailability($offer->getAvailabilityText())            // товар под заказ
            ->setDelivery($this->getDeliveryText($offer))               // товар под заказ
            ->setPickup($this->getPickupText($offer))                   // товар под заказ
            // ->setCrossSale($this->getCrossSale($offer))              // похожие товары
            ->setBundle($this->getBundle($offer));                       // с этим товаром покупают
        $fullProduct->setPictureList($this->getPictureList($product, $offer));           // картинки

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
     */
    public function getTags(Offer $offer)
    {
        $tags = [];
        if ($offer->isShare()) {
            /** @var Share $share */
            $share = $offer->getShare()->first();
            if ($share->hasLabelImage() && $tag = $this->getTagFromPng($share->getPropertyLabelImageFileSrc())) {
                $tags[] = $tag;
            }
            if ($share->hasLabel() && $tag = $this->getTagFromTitle($share->getPropertyLabel())) {
                $tags[] = $tag;
            }

            if (!$tags) {
                $tags[] = $this->getTagFromPng(MarkHelper::MARK_GIFT_IMAGE_SRC);
            }
        }
        if (
            (($offer->isHit() || $offer->isPopular()) && $tag = $this->getTagFromPng(MarkHelper::MARK_HIT_IMAGE_SRC))
            || ($offer->isNew() && $tag = $this->getTagFromPng(MarkHelper::MARK_NEW_IMAGE_SRC))
            || ($offer->isSale() && $tag = $this->getTagFromPng(MarkHelper::MARK_SALE_IMAGE_SRC))
        ) {
            $tags[] = $tag;
        }
        return $tags;
    }

    /**
     * @param string $svg
     * @return Tag|false
     */
    public function getTag(string $svg)
    {
        try {
            $png = ImageHelper::convertSvgToPng($svg);
        } catch (\ImagickException $e) {
            return false;
        } catch (NotFoundException $e) {
            return false;
        }
        return $this->getTagFromPng($png);
    }

    /**
     * @param string $png
     *
     * @return Tag
     */
    public function getTagFromPng(string $png): Tag
    {
        return (new Tag())->setImg($png);
    }

    /**
     * @param string $title
     *
     * @return Tag
     */
    public function getTagFromTitle(string $title): Tag
    {
        return (new Tag())->setTitle($title);
    }


    /**
     * @param Product $product
     * @param Offer   $offer
     * @param int     $quantity
     * @return ShortProduct
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Manzana\Exception\ExecuteErrorException
     * @throws \FourPaws\External\Manzana\Exception\ExecuteException
     */
    public function convertToShortProduct(Product $product, Offer $offer, $quantity = 1): ShortProduct
    {
        $shortProduct = (new ShortProduct())
            ->setId($offer->getId())
            ->setTitle($offer->getName())
            ->setXmlId($offer->getXmlId())
            ->setBrandName($product->getBrandName())
            ->setWebPage($offer->getCanonicalPageUrl())
            ->setColor($offer->getColorProp());

        $this->getTotalStarsAndComments($product->getId());

        if ($this->productStars) {
            $shortProduct->setTotalStars($this->productStars);
        }

        if ($this->totalComments) {
            $shortProduct->setTotalComments($this->totalComments);
        }

        // большая картинка
        if ($images = $offer->getResizeImages(static::DETAIL_PICTURE_WIDTH, static::DETAIL_PICTURE_HEIGHT)) {
            /** @var Image $picture */
            $shortProduct->setPicture($images->first());
        }

        // картинка ресайз (возможно не используется, но это не точно)
        if ($resizeImages = $offer->getResizeImages(static::LIST_IMAGE_WIDTH, static::LIST_IMAGE_HEIGHT)) {
            $shortProduct->setPicturePreview($resizeImages->first());
        }

        // цена
        $price = (new Price())
            ->setActual($offer->getPrice())
            ->setOld($offer->getOldPrice())
            ->setSubscribe($offer->getSubscribePrice());
        $shortProduct->setPrice($price);


        // ТПЗ
        $shortProduct
            ->setIsByRequest($offer->isByRequest())
            ->setIsAvailable($offer->isAvailable());

        try {
            $shortProduct->setPickupOnly(!$offer->isDeliverable() && $product->isPickupAvailable() && $offer->isPickupAvailable());
        } catch (\Exception $e) {
            $shortProduct->setPickupOnly(false);
        }

        // лейблы
        $shortProduct->setTag($this->getTags($offer));

        // бонусы
        $shortProduct->setBonusAll($offer->getBonusCount(3, $quantity));
        $shortProduct->setBonusUser($offer->getBonusCount($this->userService->getDiscount(), $quantity));

        //Округлить до упаковки
        $shortProduct->setInPack(intval($offer->getMultiplicity()));

        if ($this->stampService::IS_STAMPS_OFFER_ACTIVE) {
            // уровни скидок за марки
            $serializer         = Application::getInstance()->getContainer()->get(SerializerInterface::class);
            $currentStampsLevel = $this->stampService->getCurrentStampLevel();

            $stampLevels = [];

            foreach ($this->stampService->getExchangeRules($offer->getXmlId()) as $exchangeRule) {
                $exchangeRule['isMaxLevel'] = ($exchangeRule['stamps'] === $currentStampsLevel);
                $stampLevels[]              = $serializer->fromArray($exchangeRule, StampLevel::class);
            }

            ini_set('serialize_precision', -1); // костыль, чтобы не "портились" price в StampLevel при сериализации

            $shortProduct->setStampLevels($stampLevels); //TODO get stampLevels from Manzana. If Manzana doesn't answer then set no levels
        }

        return $shortProduct;
    }

    /**
     * @param Product   $product
     * @param Offer     $offer
     * @param bool      $needPackingVariants
     * @param bool|null $showVariantsIfOneVariant
     * @param bool|null $hasOnlyColourCombinations
     * @return FullProduct
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws SystemException
     * @throws IblockNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \FourPaws\External\Manzana\Exception\ExecuteErrorException
     * @throws \FourPaws\External\Manzana\Exception\ExecuteException
     */
    public function convertToFullProduct(Product $product, Offer $offer, $needPackingVariants = false, ?bool $showVariantsIfOneVariant = true, ?bool $hasOnlyColourCombinations = false, ?bool $detailInfo = false): FullProduct
    {
        $offer->setColor();
        $shortProduct = $this->convertToShortProduct($product, $offer);

        $fullProduct = (new FullProduct())
            ->setWeight($offer->getPackageLabel(false, 0))
            ->setHasSpecialOffer($offer->isShare());

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
            ->setIsAvailable($shortProduct->getIsAvailable())
            ->setPickupOnly($shortProduct->getPickupOnly())
            ->setInPack($shortProduct->getInPack())
            ->setStampLevels($shortProduct->getStampLevels())
            ->setColor($shortProduct->getColor());

        if ($detailInfo) {
            $fullProduct->setComments($this->getProductCommentsById($product->getId()));

            $detailText = $product->getDetailText()->getText();
            $detailText = ImageHelper::appendDomainToSrc($detailText);

            $fullProduct->setDetailsHtml($detailText);
        }

        $this->getTotalStarsAndComments($product->getId());

        if ($this->productStars) {
            $fullProduct->setTotalStarsFull($this->productStars);
        }

        if ($this->totalComments) {
            $fullProduct->setTotalCommentsFull($this->totalComments);
        }

        if ($needPackingVariants) {
            if ($hasOnlyColourCombinations) {
                $fullProduct->setColourVariants($this->getPackingVariants($product, $fullProduct, $showVariantsIfOneVariant));   // цвета
                $fullProduct->setPackingVariants($this->getPackingVariants($product, $fullProduct, $showVariantsIfOneVariant, true));
            } else {
                $fullProduct->setPackingVariants($this->getPackingVariants($product, $fullProduct, $showVariantsIfOneVariant));   // фасовки
            }
        }

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
        $allDeliveryCodes    = array_merge(DeliveryService::DELIVERY_CODES, DeliveryService::PICKUP_CODES);
        $deliveries          = $this->deliveryService->getByLocation($userLocationCode, $allDeliveryCodes);
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
        if (!($deliveryResult && ($deliveryResult instanceof DeliveryResult || $deliveryResult instanceof PickupResult))) {
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
            return $pickupResult->getDeliveryCode() === DeliveryService::INNER_PICKUP_CODE
                ? $pickupResult->getTextForOffer(false)
                : '';
        }
        return '';
    }

    /**
     * Фасовки товара
     *
     * @param Product     $product
     * @param FullProduct $currentFullProduct
     * @param bool|null   $showVariantsIfOneVariant
     * @param bool|null   $onlyCurrentOffer
     *
     * @return array
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getPackingVariants(Product $product, FullProduct $currentFullProduct, ?bool $showVariantsIfOneVariant = true, ?bool $onlyCurrentOffer = false): array
    {
        $offers = $product->getOffersSorted();
        // если в предложениях только текущий продукт
        $hasOnlyCurrentOffer = (count($offers) === 1 && $offers->current()->getId() === $currentFullProduct->getId());
        if ($hasOnlyCurrentOffer && $showVariantsIfOneVariant) {
            return [$fullProduct = $this->convertToFullProduct($product, $offers->current())];
        }
        if (empty($offers) || ($hasOnlyCurrentOffer && !$showVariantsIfOneVariant)
            || ($onlyCurrentOffer && !$showVariantsIfOneVariant)
        ) {
            return [];
        }

        $packingVariants = [];
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            if ($onlyCurrentOffer && $offer->getId() != $currentFullProduct->getId()) {
                continue;
            }
            // if ($offer->getId() === $currentFullProduct->getId()) {
            // toDo если переиспользовать $currentFullProduct - в массиве $packingVariants в итоге попадает null вместо объекта
            //    $fullProduct = clone $currentFullProduct;
            // } else {
            // toDo рефакторинг костыля
            // костыль потому что в allStocks вместо объекта StockCollection приходит просто массив с кодами магазинов...
            // взято из метода FourPaws\Catalog\Model\getAllStocks()
            $stockService = Application::getInstance()->getContainer()->get(StockService::class);
            $offer->withAllStocks($stockService->getStocksByOffer($offer));
            // end костыль
            $fullProduct = $this->convertToFullProduct($product, $offer);

            $fullProduct->setComments([]);
            // $fullProduct->setTotalStars($this->productStars);
            // $fullProduct->setTotalComments($this->totalComments);
            // }
            $packingVariants[] = $fullProduct;
        }
        return $packingVariants;
    }

    /**
     * Акция товара
     * @param Offer $offer
     * @return FullProduct\SpecialOffer|null
     */
    public function getSpecialOffer(Offer $offer)
    {
        /** @var Share $specialOfferModel */
        $specialOfferModel = $offer->getShare()->current();
        $specialOffer      = (new FullProduct\SpecialOffer());
        if (!$specialOfferModel) {
            return null;
        }
        $specialOffer
            ->setId($specialOfferModel->getId())
            ->setName($specialOfferModel->getName())
            ->setDescription(strip_tags(html_entity_decode($specialOfferModel->getPreviewText())));
        $specialOffer->setImage($specialOfferModel->getDetailPictureSrc());

        if ($specialOfferModel->getDateActiveFrom() && $specialOfferModel->getDateActiveTo()) {
            $dateFrom = DateHelper::replaceRuMonth($specialOfferModel->getDateActiveFrom()->format('d #n# Y'), DateHelper::GENITIVE);
            $dateTo   = DateHelper::replaceRuMonth($specialOfferModel->getDateActiveTo()->format('d #n# Y'), DateHelper::GENITIVE);
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
     * Цвета товара
     * @param Offer $offer
     * @return FullProduct\Flavour[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function getColours(Offer $offer): array
    {
        if (!empty($offer->getColourCombination()) && $offer->getColor()) {
            $unionOffers = $this->getOffersByUnion('color', $offer->getColourCombinationXmlId());
            if (!$unionOffers->isEmpty()) {
                $unionOffersSorted = [];
                /** @var Offer $unionOffer */
                foreach ($unionOffers as $unionOffer) {
                    $unionOffersSorted[$unionOffer->getColorWithSize()] = $unionOffer;
                }
                $this->sortService->colorWithSizeSort($unionOffersSorted);

                $colours = [];
                foreach ($unionOffersSorted as $unionOffer) {
                    $color             = $unionOffer->getColor();
                    $fullProductColour = (new FullProduct\Colour())
                        ->setOfferId($unionOffer->getId())
                        ->setTitle($unionOffer->getColorWithSize());
                    if ($color) {
                        $hexCode  = $color->getColorCode();
                        $imageUrl = $color->getFilePath();

                        $fullProductColour
                            ->setHexCode($hexCode);

                        if ($imageUrl) {
                            $fullProductColour
                                ->setImageUrl((new FullHrefDecorator($imageUrl))->getFullPublicPath());
                        }
                    }
                    $colours[] = $fullProductColour;
                }
                return $colours;
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
                $crossSale[]     = (new FullProduct\CrossSale())
                    ->setOfferId($bundleItemOffer->getId())
                    ->setTitle($bundleItemOffer->getName())
                    ->setPrice($bundleItemOffer->getPrice())
                    ->setImage($bundleItemOffer->getImages()->current());
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
     */
    public function getBundle(Offer $offer)
    {
        $bundleItems   = [];
        $oldTotalPrice = 0;
        $totalPrice    = 0;
        $bonusAmount   = 0;
        if (!$bundle = $offer->getBundle()) {
            return null;
        }
        $percent = $this->userService->getCurrentUserBonusPercent();
        /** @var BundleItem $bundleItem */
        foreach ($bundle->getProducts() as $bundleItem) {
            $bundleItemOffer   = $bundleItem->getOffer();
            $bundleItemProduct = $bundleItemOffer->getProduct();

            $product = $this->convertToShortProduct($bundleItemProduct, $bundleItemOffer);

            $bundleItems[] = $product;

            $totalPrice    += $bundleItemOffer->getCatalogPrice() * $bundleItem->getQuantity();
            $oldTotalPrice += $bundleItemOffer->getCatalogOldPrice() * $bundleItem->getQuantity();
            $bonusAmount   += $bundleItemOffer->getBonusCount($percent, $bundleItem->getQuantity());
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
     * Возвращает строку "k товаров (n кг) на сумму m ₽"
     * Используется при чекауте
     * @param $quantity
     * @param $weight
     * @param $price
     * @return string
     */
    public static function getGoodsTitleForCheckout(int $quantity, float $weight, int $price): string
    {
        if ($quantity === 0) {
            return '';
        }
        return $quantity
            . ' ' . WordHelper::declension($quantity, ['товар', 'товара', 'товаров'])
            . ' (' . WordHelper::showWeight($weight, true) . ') '
            . 'на сумму ' . CurrencyHelper::formatPrice($price, false);
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
        $unionOffers     = [];
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

    protected function getPictureList(Product $product, ?Offer $currentOffer)
    {
        $offers = $product->getOffersSorted();
        if (empty($offers)) {
            return [];
        }

        $images     = [];
        $addInStart = [];

        /** @var Offer $offer */
        foreach ($offers as $offer) {
            if ($offerImages = $offer->getResizeImages(static::DETAIL_PICTURE_WIDTH, static::DETAIL_PICTURE_HEIGHT)) {
                foreach ($offerImages as $image) {
                    if ($currentOffer->getColor() && ($currentOffer->getColor()->getColorCode() !== $offer->getColor()->getColorCode())) {
                        $images[] = $image;
                    } else {
                        $addInStart[] = $image;
                    }
                }
            }
        }

        if (!empty($addInStart)) {
            $addInStart = array_unique($addInStart);

            foreach ($addInStart as $addInStartItem) {
                array_unshift($images, $addInStartItem);
            }
        }

        return array_unique($images);
    }

    /**
     * @param int $stockId
     * @return array
     * @throws IblockNotFoundException
     */
    public function getProductIdsByShareId(int $stockId)
    {
        $res = \CIBlockElement::GetProperty(IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES), $stockId, '', '', ['CODE' => 'PRODUCTS']);
        $xmlIds = [];
        while ($item = $res->Fetch()) {
            if (!empty($item['VALUE']) && !\in_array($item['VALUE'], $xmlIds, true)) {
                $xmlIds[] = $item['VALUE'];
            }
        }

        $query             = new ProductQuery();
        $productCollection = $query->withFilter([
            '=XML_ID'          => $xmlIds,
            'ACTIVE'           => 'Y',
            '>CATALOG_PRICE_2' => 0,
        ])->withSelect(['ID'])->exec();
        $productIds        = [];
        foreach ($productCollection as $product) {
            $productIds[] = $product->getId();
        }

        return $productIds;
    }

    /**
     * @param int $stockId
     * @return array
     * @throws IblockNotFoundException
     */
    public function getProductXmlIdsByShareId(int $stockId)
    {
        $cache = new FilesystemCache('', 3600 * 24 * 3);

        $cacheKey = 'share_' . $stockId;

        if (!$cache->has($cacheKey)) {
            $share = (new ShareQuery())
                ->withFilter(['ID' => $stockId])
                ->exec()
                ->first();

            $cache->set($cacheKey, $share);
        } else {
            $share = $cache->get($cacheKey);
        }


//        $shareCache = (new BitrixCache())
//            ->withId($cacheKey)
//            ->withTime(864000)
//            ->resultOf(function () use ($stockId) {
//                $share = (new ShareQuery())
//                    ->withFilter(['ID' => $stockId])
//                    ->exec()
//                    ->first();
//
//                return $share;
//            });
//
//        $share = $shareCache['result'];

        $xmlIds = [];

        if ($share) {
            $xmlIds = $share->getPropertyProducts();
        }

        return $xmlIds;
    }

    /**
     * @param $shareId
     * @return bool
     * @throws ApplicationCreateException
     * @throws IblockNotFoundException
     */
    public function checkShareAccess($shareId)
    {
        return $this->basketRulesService->checkRegionAccess($shareId);
    }

    /**
     * @return array
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     */
    public function getStampsCategories()
    {
        $elementIblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::CATALOG_SLIDER_PRODUCTS);

        // получаем id разделов и xml_id торговых предложений
        $sectionOffersXmlIds = []; // массив соответсвия раздела и его ТП
        $offerXmlIds         = []; // массив для дальнейшего получения ТП

        $rsElement = \CIBlockElement::GetList(['SORT' => SORT_ASC], ['IBLOCK_ID' => $elementIblockId, '=ACTIVE' => BaseEntity::BITRIX_TRUE, '=SECTION_CODE' => 'stamps'], false, false,
            ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_SECTION', 'PROPERTY_PRODUCTS']);

        $sections = [];
        while ($arElement = $rsElement->Fetch()) {
            if ($arElement['PROPERTY_SECTION_VALUE']) {
                if (!isset($sectionOffersXmlIds[$arElement['PROPERTY_SECTION_VALUE']])) {
                    $sectionOffersXmlIds[$arElement['PROPERTY_SECTION_VALUE']] = [];
                }

                if (!isset($sections[$arElement['PROPERTY_SECTION_VALUE']])) {
                    $sections[$arElement['PROPERTY_SECTION_VALUE']] = $arElement['NAME'];
                }


                if ($arElement['PROPERTY_PRODUCTS_VALUE']) {
                    $sectionOffersXmlIds[$arElement['PROPERTY_SECTION_VALUE']][] = $arElement['PROPERTY_PRODUCTS_VALUE'];
                    $offerXmlIds[]                                               = $arElement['PROPERTY_PRODUCTS_VALUE'];
                }
            }
        }

        // получаем торговые предложения
        $offers = [];

        if (!empty($offerXmlIds)) {
            $offersListCollection = $this->getListFromXmlIds($offerXmlIds, true);
            $offersList           = $offersListCollection->get(0) ?? [];

            /** @var Offer $offer */
            foreach ($offersList as $offer) {
                $offers[$offer->getXmlId()] = $offer;
            }
        }

        // заполняем итоговый массив
        $stampCategories = [];

        foreach ($sectionOffersXmlIds as $sectionId => $offerXmlIds) {
            $goods = [];

            foreach ($offerXmlIds as $offerXmlId) {
                if (isset($offers[$offerXmlId])) {
                    $goods[] = $offers[$offerXmlId];
                }
            }

            $stampCategories[] = [
                'id'    => $sectionId,
                'title' => $sections[$sectionId],
                'goods' => $goods,
            ];
        }

        return $stampCategories;
    }

    public function getProductCommentsById($id, $limit = 2, $offset = 0)
    {
        try {
            Loader::includeModule('articul.main');

            $comments = CommentsTable::query()
                ->setSelect(['stars' => 'UF_MARK', 'text' => 'UF_TEXT', 'date' => 'UF_DATE', 'images' => 'UF_PHOTOS', 'author' => 'UF_USER_ID'])
                ->setFilter(['=UF_OBJECT_ID' => $id, '=UF_ACTIVE' => 1])
                ->setLimit($limit)
                ->setOffset($offset)
                ->setOrder(['ID' => 'DESC'])
                ->exec()
                ->fetchAll();

            $result = $this->buildCommentsFieldResult($comments);

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAllProductCommentsWithNav($id, $limit = 2, $page = 1)
    {
        try {
            Loader::includeModule('articul.main');

            $nav = new \Bitrix\Main\UI\PageNavigation('nav-comments');

            $nav->allowAllRecords(true)
                ->setPageSize($limit)
                ->setCurrentPage($page);

            $limit  = $nav->getLimit();
            $offset = $nav->getOffset();

            $comments = CommentsTable::query()
                ->setSelect(['stars' => 'UF_MARK', 'text' => 'UF_TEXT', 'date' => 'UF_DATE', 'images' => 'UF_PHOTOS', 'author' => 'UF_USER_ID'])
                ->setFilter(['=UF_OBJECT_ID' => $id, '=UF_ACTIVE' => 1])
                ->setLimit($limit)
                ->setOffset($offset)
                ->setOrder(['ID' => 'DESC'])
                ->setCacheTtl('36000')
                ->exec()
                ->fetchAll();

            $result = $this->buildCommentsFieldResult($comments);

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function addProductComment($request)
    {
        $user   = $this->userService->getCurrentUser();
        $images = $this->getImagesId($request->files->get('images'));
        $id = \CCatalogSku::GetProductInfo($request->get('id'))['ID'];

        CommentsTable::add(
            [
                'UF_USER_ID'   => $user->getId(),
                'UF_TEXT'      => $request->get('text'),
                'UF_MARK'      => $request->get('stars'),
                'UF_ACTIVE'    => 0,
                'UF_OBJECT_ID' => $id,
                'UF_TYPE'      => 'catalog',
                'UF_DATE'      => new \Bitrix\Main\Type\Date(),
                'UF_PHOTOS' => serialize($images),
            ]
        );
    }

    private function buildCommentsFieldResult($comments)
    {
        foreach ($comments as &$comment) {
            $serializedId = unserialize($comment['images']);

            $paths = $this->getImagePaths($serializedId);

            $comment['date']   = $comment['date']->format("d.m.Y");
            $comment['author'] = $this->getUserById($comment['author']);
            $comment['images'] = $this->getImagePaths($serializedId); //$this->codeImagesToBase64($paths);
        }

        return $comments;
    }

    private function getImagePaths($serializedId)
    {
        $paths = [];

        foreach ($serializedId as $key => $imgId) {
            $paths[] = getenv('SITE_URL') . \CFile::GetPath($imgId);
        }

        return $paths;
    }

    private function codeImagesToBase64($paths)
    {
        $result = [];

        foreach ($paths as $path) {
            $type     = pathinfo($path, PATHINFO_EXTENSION);
            $data     = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $path);
            $result[] = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        return $result;
    }

    private function getUserById($id)
    {
        $user = \Bitrix\Main\UserTable::query()
            ->setSelect(['NAME', 'LAST_NAME'])
            ->setFilter(['=ID' => $id])
            ->exec()
            ->fetch();

        $result = $user['NAME'] . ' ' . $user['LAST_NAME'];

        return $result;
    }

    private function getImagesId($images)
    {
        $result = [];

        foreach ($images as $image) {
            $fileArray = \CFile::MakeFileArray($image->getPathName());

            if (in_array($image->getClientOriginalExtension(), ['jpg', 'jpeg', 'gif', 'png']))
            $fileArray['name'] .= '.' . $image->getClientOriginalExtension();

            $result[] = \CFile::SaveFile($fileArray, 'comments_temp_files');
        }

        return $result;
    }

    private function getTotalStarsAndComments($id)
    {
        $stars = CommentsTable::query()
            ->setSelect(['UF_MARK'])
            ->setFilter(['=UF_OBJECT_ID' => $id, '=UF_ACTIVE' => 1])
            ->setCacheTtl('36000')
            ->exec();

        while ($star = $stars->fetch()['UF_MARK']) {
            $elements[] = $star;
        }

        $count = count($elements);

        $this->totalComments = $count;
        $this->productStars  = (int)(array_sum($elements) / $count);
    }
}
