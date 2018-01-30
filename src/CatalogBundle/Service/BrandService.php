<?php

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\Catalog\Exception\BrandMoreOneFoundException;
use FourPaws\Catalog\Exception\BrandNotFoundException;
use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\BrandQuery;

class BrandService
{
    /**
     * @param string $brandCode
     *
     * @throws BrandNotFoundException
     * @return Brand
     * @throws BrandMoreOneFoundException
     */
    public function getByCode(string $brandCode): Brand
    {
        $brandCollection = (new BrandQuery())->withFilterParameter('=CODE', $brandCode)->exec();

        if ($brandCollection->isEmpty()) {
            throw new BrandNotFoundException(
                sprintf('Бренд с кодом `%s` не найден.', $brandCode)
            );
        }

        if ($brandCollection->count() > 1) {
            throw new BrandMoreOneFoundException(
                sprintf('Найдено более одного бренда с кодом `%s`', $brandCode)
            );
        }

        return $brandCollection->current();
    }

    /**
     * @param Brand $brand
     * @throws IblockNotFoundException
     * @return Category
     */
    public function getBrandCategory(Brand $brand): Category
    {
        return Category::createRoot()
            ->withName(sprintf('Товары бренда %s', $brand->getName()));
    }
}
