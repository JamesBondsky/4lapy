<?php

namespace FourPaws\Test\AppManager;

use FourPaws\Catalog\Collection\BrandCollection;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\BrandFilter;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Model\Filter\PetAgeFilter;
use FourPaws\Catalog\Model\Filter\PetSizeFilter;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Catalog\Query\BrandQuery;
use FourPaws\Catalog\Query\CategoryQuery;
use PHPUnit\Framework\Assert;

class CatalogHelper
{
    /**
     * @return Category
     * @throws \PHPUnit_Framework_Exception
     */
    public function getExistingCategory(): Category
    {
        //TODO А можно рандомную секцию каждый раз?
        /** @var Category $category */
        $category = (new CategoryQuery())->withFilterParameter('=DEPTH_LEVEL', 3)
                                         ->withNav(['nTopCount' => 1])
                                         ->exec()
                                         ->current();

        Assert::assertInstanceOf(
            Category::class,
            $category,
            'Expected category should exists.'
        );

        return $category;
    }

    /**
     * @param int $count
     *
     * @return BrandCollection
     */
    public function getRandomBrands(int $count): BrandCollection
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new BrandQuery())->withNav(['nTopCount' => $count])
                                 ->withOrder(['RAND' => 'ASC'])
                                 ->exec();
    }

    /**
     * Выбирает N случайных значений из фильтра и возвращает их
     *
     * @param \FourPaws\Catalog\Model\Filter\FilterInterface $filter
     *
     * @param $count
     *
     * @return VariantCollection
     */
    public function getRandomCheckedVariants(FilterInterface $filter, int $count): VariantCollection
    {
        $allVariants = $filter->getAllVariants()->toArray();

        $checkedVariantKeys = array_rand($allVariants, $count);

        if ($count == 1) {
            $checkedVariantKeys = [$checkedVariantKeys];
        }

        $checkedVariants = new VariantCollection();

        foreach ($checkedVariantKeys as $key) {
            /** @var \FourPaws\Catalog\Model\Variant $variant */
            $variant = $allVariants[$key];
            $checkedVariants->add($variant->withChecked(true));
        }

        return $checkedVariants;

    }

    /**
     * Возвращает параметры запроса для случайным образом выбранных фильтров.
     *
     * @return array
     */
    public function getRandomFiltersQueryParams(): array
    {
        $params = [];

        /**
         * Выбрать три случайных бренда
         */
        $brandFilter = new BrandFilter();
        $checkedBrands = $this->getRandomCheckedVariants($brandFilter, 3);
        $checkedBrandValues = array_map(
            function (Variant $variant) {
                return $variant->getValue();
            },
            $checkedBrands->toArray()
        );
        $params[$brandFilter->getFilterCode()] = implode(',', $checkedBrandValues);

        /**
         * Выбрать 1 случайный возраст животного
         */
        $petAgeFilter = new PetAgeFilter();
        $checkedPetAges = $this->getRandomCheckedVariants($petAgeFilter, 1);
        $checkedPetAgeValues = array_map(
            function (Variant $variant) {
                return $variant->getValue();
            },
            $checkedPetAges->toArray()
        );
        $params[$petAgeFilter->getFilterCode()] = implode(',', $checkedPetAgeValues);

        /**
         * Выбрать 2 случайных размера животного
         */
        $petSizeFilter = new PetSizeFilter();
        $checkedPetSizes = $this->getRandomCheckedVariants($petSizeFilter, 2);
        $checkedPetSizeValues = array_map(
            function (Variant $variant) {
                return $variant->getValue();
            },
            $checkedPetSizes->toArray()
        );
        $params[$petSizeFilter->getFilterCode()] = implode(',', $checkedPetSizeValues);

        return $params;
    }
}
