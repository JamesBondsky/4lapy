<?php

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\RangeFilterInterface;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\IblockHelper;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\Filter;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FilterVariant;
use FourPaws\MobileApiBundle\Exception\AccessDeinedException;
use FourPaws\MobileApiBundle\Exception\CategoryNotFoundException as MobileCategoryNotFoundException;
use FourPaws\MobileApiBundle\Exception\SystemException;
use FourPaws\Search\Model\Navigation;
use FourPaws\Search\SearchService;
use Psr\Log\LoggerAwareTrait;

class CatalogService
{
    use LazyLoggerAwareTrait;

    /**
     * @var CategoriesService
     */
    private $categoriesService;

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
        SearchService $searchService,
        ProductService $productService
    )
    {
        $this->categoriesService = $categoriesService;
        $this->searchService = $searchService;
        $this->productService = $productService;
    }

    /**
     * @param int $categoryId
     * @param int $stockId
     * @return ArrayCollection
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getFilters(int $categoryId = 0, int $stockId = 0)
    {
        try {
            $category = $this->categoriesService->getById($categoryId);
        } catch (CategoryNotFoundException $e) {
            $this->log()->debug($e->getMessage());
            throw new MobileCategoryNotFoundException('Category not found');
        } catch (\Exception $exception) {
            throw new SystemException($exception->getMessage());
        }

        $productIds = '';
        if($stockId > 0){
            if(!$this->productService->checkShareAccess($stockId)){
                throw new AccessDeinedException('Access denied');
            }
            $productIds = $this->productService->getProductXmlIdsByShareId($stockId);
        }

        /** we have to search for products to calculate amount of products per each filter  */
        $this->searchService->searchProducts(
            $category->getFilters(),
            new Sorting(),
            new Navigation(),
            $productIds
        );

        $filters = $category
            ->getFilters()
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
                        (new ArrayCollection($filter->getAvailableVariants()->toArray()))
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

}
