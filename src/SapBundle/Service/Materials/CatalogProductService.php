<?php

namespace FourPaws\SapBundle\Service\Materials;

use FourPaws\BitrixOrm\Model\CatalogProduct;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Repository\CatalogProductRepository;

class CatalogProductService
{
    /**
     * @var CatalogProductRepository
     */
    private $catalogProductRepository;

    public function __construct(CatalogProductRepository $catalogProductRepository)
    {
        $this->catalogProductRepository = $catalogProductRepository;
    }

    /**
     * @param int      $offerId
     * @param Material $material
     *
     * @return bool
     */
    public function processMaterial(int $offerId, Material $material): bool
    {
        $catalogProduct = $this->createFromMaterial($material)->setId($offerId);
        return $this->catalogProductRepository->createOrUpdate($catalogProduct);
    }

    /**
     * @param Material $material
     *
     * @return CatalogProduct
     */
    protected function createFromMaterial(Material $material): CatalogProduct
    {
        $catalogProduct = new CatalogProduct();
        $basicUom = $material->getBasicUnitOfMeasure();
        return $catalogProduct
            ->setWidth($basicUom->getWidth() * 1000)
            ->setHeight($basicUom->getHeight() * 1000)
            ->setLength($basicUom->getLength() * 1000)
            ->setWeight($basicUom->getGrossWeight() * 1000);
    }
}
