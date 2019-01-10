<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\RangeFilterInterface;
use FourPaws\Catalog\Model\Variant;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\Filter;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FilterVariant;
use FourPaws\MobileApiBundle\Exception\CategoryNotFoundException as MobileCategoryNotFoundException;
use FourPaws\MobileApiBundle\Exception\SystemException;

class CatalogService
{
    /**
     * @var CategoriesService
     */
    private $categoriesService;

    /**
     * @var FilterService
     */
    private $filterService;

    public function __construct(
        CategoriesService $categoriesService,
        FilterService $filterService
    )
    {
        $this->categoriesService = $categoriesService;
        $this->filterService = $filterService;
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
     * @param array $filters
     * @return array
     */
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

}
