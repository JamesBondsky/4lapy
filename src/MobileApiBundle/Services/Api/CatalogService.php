<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\Catalog\Model\Bundle as BundleModel;
use FourPaws\Catalog\Model\BundleItem;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\RangeFilterInterface;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterHelper;
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\CatalogBundle\Service\SortService;
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
use FourPaws\Search\Model\Navigation;
use FourPaws\Search\SearchService;
use Psr\Log\LoggerAwareInterface;
use FourPaws\Catalog\Model\Product as ProductModel;
use FourPaws\Catalog\Model\Offer as OfferModel;
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

    public function __construct(
        CategoriesService $categoriesService,
        FilterService $filterService,
        FilterHelper $filterHelper,
        SortService $sortService,
        SearchService $searchService
    )
    {
        $this->categoriesService = $categoriesService;
        $this->filterService = $filterService;
        $this->filterHelper = $filterHelper;
        $this->sortService = $sortService;
        $this->searchService = $searchService;
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
                            ->map(function (Variant $variant) {
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
     * @return ArrayCollection
     * @throws CategoryNotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public function getProductsList(
        Request $request,
        int $categoryId,
        string $sort = 'popular',
        int $count = 10,
        int $page = 1
    ): ArrayCollection
    {
        $category = $this->categoriesService->getById($categoryId);
        $this->filterHelper->initCategoryFilters($category, $request);
        $filters = $category->getFilters();

        $sort = $this->sortService->getSorts($sort)->getSelected();

        $nav = (new Navigation())
            ->withPage($page)
            ->withPageSize($count);

        $productsSearchResult = $this->searchService->searchProducts($filters, $sort, $nav);
        $productCollection = $productsSearchResult->getProductCollection();

        return (new ArrayCollection([
            'products' => $productCollection->map(\Closure::fromCallable([$this, 'mapProduct']))->getValues(),
            'cdbResult' => $productCollection->getCdbResult()
        ]));
    }

    /**
     * @param int $id
     * @return mixed
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getOffer(int $id)
    {
        $currentOffer = (new OfferQuery())->getById($id);
        if (!$currentOffer) {
            throw new NotFoundProductException("Предложение с ID $id не найдено");
        }
        $productModel = $currentOffer->getProduct();
        $offers = $productModel->getOffersSorted();

        $product = (new FullProduct())
            ->setDetailsHtml([$productModel->getDetailText()->getText()])
            ->setNutritionRecommendations($productModel->getNormsOfUse()->getText())
            ->setNutritionFacts($productModel->getComposition()->getText());

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
            $product->setPackingVariants($packingVariants);
        }

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
            $product->setSpecialOffer($specialOffer);
        }

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
                $product->setFlavours($flavours);
            }
        }

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
            $product->setBundle($crossSale);
        }

        $product
            ->setId($currentOffer->getId())
            ->setTitle($productModel->getName())
            ->setXmlId($productModel->getXmlId())
            ->setBrandName($productModel->getBrandName())
            ->setWebPage($productModel->getCanonicalPageUrl());

        $product->setPicture($currentOffer->getImages() ? $currentOffer->getImages()->first() : '');
        $product->setPicturePreview($currentOffer->getResizeImages(200, 250) ? $currentOffer->getResizeImages(200, 250)->first() : '');

        $price = (new Price())
            ->setActual($currentOffer->getPrice())
            ->setOld($currentOffer->getOldPrice());
        $product->setPrice($price);


        return $product;
    }

    /**
     * @param ProductModel $productModel
     * @return FullProduct
     * @throws \Bitrix\Main\SystemException
     */
    protected function mapProduct(ProductModel $productModel): FullProduct
    {
        $product = (new FullProduct())
            ->setDetailsHtml([$productModel->getDetailText()->getText()])
            ->setId($productModel->getId())
            ->setTitle($productModel->getName())
            ->setXmlId($productModel->getXmlId())
            ->setBrandName($productModel->getBrandName())
            ->setWebPage($productModel->getCanonicalPageUrl());

        /** @var OfferModel $currentOffer */
        if ($currentOffer = $this->getCurrentOffer($productModel)) {
            $product->setPicture($currentOffer->getImages() ? $currentOffer->getImages()->first() : '');
            $product->setPicturePreview($currentOffer->getResizeImages(200, 250) ? $currentOffer->getResizeImages(200, 250)->first() : '');
            $price = (new Price())
                ->setActual($currentOffer->getOldPrice())
                ->setOld($currentOffer->getPrice());
            $product->setPrice($price);
        }

        return $product;
    }

    /**
     * @param ProductModel $productModel
     *
     * @param array $offerFilter
     * @return mixed|null
     */
    protected function getCurrentOffer(ProductModel $productModel, $offerFilter = [])
    {
        $productModel->getOffers(true, $offerFilter);
        $offers = $productModel->getOffersSorted();
        /** @var OfferModel $offerModel */
        $foundOfferWithImages = false;
        $currentOffer = $offers->last();
        foreach ($offers as $offer) {
            $offer->setProduct($productModel);

            if (!$foundOfferWithImages || $offer->getImagesIds()) {
                $currentOffer = $offer;
            }
        }
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
