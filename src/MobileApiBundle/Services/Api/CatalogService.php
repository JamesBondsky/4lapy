<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\Catalog\Model\BundleItem;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\RangeFilterInterface;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterHelper;
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\CatalogBundle\Service\SortService;
use FourPaws\Search\Model\Navigation;
use FourPaws\Helpers\DateHelper;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\Filter;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FilterVariant;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\PackingVariant;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\SpecialOffer;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\Flavour;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\Bundle;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Exception\CategoryNotFoundException as MobileCategoryNotFoundException;
use FourPaws\MobileApiBundle\Exception\NotFoundProductException;
use FourPaws\MobileApiBundle\Exception\SystemException;
use FourPaws\Search\SearchService;
use FourPaws\StoreBundle\Service\StockService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Request;

class CatalogService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var CategoriesService
     */
    private $categoriesService;

    /**
     * @var FilterService
     */
    private $filterService;

    /**
     * @var FilterHelper
     */
    private $filterHelper;

    /**
     * @var SortService
     */
    private $sortService;

    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var ProductService
     */
    private $productService;

    public function __construct(
        CategoriesService $categoriesService,
        FilterService $filterService,
        FilterHelper $filterHelper,
        SortService $sortService,
        SearchService $searchService,
        ProductService $productService
    )
    {
        $this->categoriesService = $categoriesService;
        $this->filterService = $filterService;
        $this->filterHelper = $filterHelper;
        $this->sortService = $sortService;
        $this->searchService = $searchService;
        $this->productService = $productService;
    }

    /**
     * @param int $categoryId
     *
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @throws MobileCategoryNotFoundException
     * @return Collection|Filter[]
     */
    public function getFilters(int $categoryId)
    {
        try {
            $category = $this->categoriesService->getById($categoryId);
        } catch (CategoryNotFoundException $e) {
            $this->log()->debug($e->getMessage());
            throw new MobileCategoryNotFoundException('Category not found');
        } catch (\Exception $exception) {
            throw new SystemException($exception->getMessage());
        }


        $filters = $this
            ->filterService->getCategoryFilters($category)
            ->getFiltersToShow();
        return (new ArrayCollection($filters->toArray()))
            ->map(function (FilterBase $filter) {
                $apiFilter = (new Filter())
                    ->setId($filter->getFilterCode())
                    ->setName($filter->getName());
                if ($filter instanceof RangeFilterInterface) {
                    $apiFilter
                        ->setMin($filter->getMinValue())
                        ->setMax($filter->getMaxValue());
                } else {
                    $apiFilter->setValues(
                        (new ArrayCollection($filter->getAllVariants()->toArray()))
                            ->map(/**
                             * @param Variant $variant
                             * @return FilterVariant
                             */
                                function (Variant $variant) {
                                return new FilterVariant($variant->getValue(), $variant->getName());
                            })
                    );
                }
                return $apiFilter;
            })
            ->filter(function ($data) {
                return $data instanceof Filter;
            });
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
    public function getProductsList(
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
            'products' => $productCollection->map(\Closure::fromCallable([$this, 'mapProduct']))->getValues(),
            'cdbResult' => $productCollection->getCdbResult()
        ]));
    }

    /**
     * @param int $id
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getOffer(int $id)
    {
        $currentOffer = (new OfferQuery())->getById($id);
        if (!$currentOffer) {
            throw new NotFoundProductException("Предложение с ID $id не найдено");
        }
        $product = $currentOffer->getProduct();
        $offers = $product->getOffersSorted();

        $fullProduct = $this->productService->convertToFullProduct($product, $currentOffer);
        $fullProduct
            ->setNutritionRecommendations($product->getNormsOfUse()->getText())
            ->setNutritionFacts($product->getComposition()->getText());

        // фасовки
        if (!empty($offers)) {
            $packingVariants = [];
            /** @var Offer $productOffer */
            foreach ($offers as $productOffer) {
                $packingVariants[] = (new PackingVariant())
                    ->setPrice($productOffer->getPrice())
                    ->setOfferId($productOffer->getId())
                    ->setWeight($productOffer->getPackageLabel(false, 0))
                    ->setHasSpecialOffer($productOffer->hasAction());
            }
            $fullProduct->setPackingVariants($packingVariants);
        }

        // акция
        /** @var Share $specialOfferModel */
        $specialOfferModel = $currentOffer->getShare()->current();
        if ($specialOfferModel) {
            $specialOffer = (new SpecialOffer())
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
            $fullProduct->setSpecialOffer($specialOffer);
        }

        // вкус
        if (!empty($currentOffer->getFlavourCombination())) {
            $unionOffers = $this->getOffersByUnion('flavour', $currentOffer->getFlavourCombination());
            if (!$unionOffers->isEmpty()) {
                $unionOffersSorted = [];
                foreach ($unionOffers as $unionOffer) {
                    $unionOffersSorted[$unionOffer->getFlavourWithWeight()] = $unionOffer;
                }
                ksort($unionOffersSorted);
                $flavours = [];
                foreach ($unionOffersSorted as $flavourWithWeight => $unionOffer) {
                    $flavours[] = (new Flavour())
                        ->setOfferId($unionOffer->getId())
                        ->setTitle($flavourWithWeight);
                }
                $fullProduct->setFlavours($flavours);
            }
        }

        // с этим товаром покупают
        if ($bundle = $currentOffer->getBundle()) {
            $crossSale = [];
            /** @var BundleItem $bundleItem */
            foreach ($bundle->getProducts() as $bundleItem) {
                $bundleItemOffer = $bundleItem->getOffer();
                $crossSale[] = (new Bundle())
                    ->setOfferId($bundleItemOffer->getId())
                    ->setTitle($bundleItemOffer->getName())
                    ->setPrice($bundleItemOffer->getPrice())
                    ->setImage($bundleItemOffer->getImages()->current())
                ;
            }
            $fullProduct->setBundle($crossSale);
        }

        return $fullProduct;
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
    protected function mapProduct(Product $product): FullProduct
    {
        /** @var Offer $currentOffer */
        $currentOffer = $this->getCurrentOffer($product);
        return $this->productService->convertToFullProduct($product, $currentOffer);
    }

    /**
     * @param Product $product
     *
     * @param array $offerFilter
     * @return mixed|null
     */
    protected function getCurrentOffer(Product $product, $offerFilter = [])
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

    protected function prepareFiltersForList(array $filters): array
    {
        $result = [];
        $delivery = false;
        $deliverySam = false;
        foreach ($filters as $filter) {
            if ($filter instanceof Filter) {
                $filterId = $filter->getId();
                $filterValue = $filter->getValue();
                $result['PROPERTY_' . $filterId] = $filterValue;
                if ($filterId == 'base') {
                    $result['><CATALOG_PRICE_1'] = $filterValue;
                } else {
                    $result['PROPERTY_' . $filterId] = $filterValue;
                    // toDo
                    // if ($filterId == 'purchase') {
                    // $delivery = $filterValue['0'] ? true : false;
                    // $deliverySam = $filterValue['1'] ? true:  false;
                    // }
                }
            }
        }
        //модифицируем фильтр по наличию товара, если пришли фильтры "Доступно для доставки" или "Доступно для самовывоза"
        // toDo
        /*
        if (($delivery or $deliverySam) and !empty($arInput['city_id'])) {
            if ($delivery and $deliverySam) {
                $filters['0']['LOGIC'] = 'AND';
            } else if ($delivery) {
                unset($filters['0']);
                $filters[">PROPERTY_STOCK"] = '0';
            } else if ($deliverySam) {
                unset($filters['0']['CS']);
            }
        }
        */

        return $result;
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
        if (!isset($this->unionOffers[$type][$val])) {
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

        }

        return $unionOffers[$type][$val];
    }
}
