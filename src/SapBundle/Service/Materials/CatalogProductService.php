<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

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

    /**
     * @var int
     */
    private $defaultVatId;

    public function __construct(CatalogProductRepository $catalogProductRepository)
    {
        $this->catalogProductRepository = $catalogProductRepository;

        $vatList = \CCatalogVat::GetListEx();
        while ($vat = $vatList->Fetch()) {
            if ((int)$vat['RATE'] === 18) {
                $this->defaultVatId = $vat['ID'];
                break;
            }
        }
    }

    /**
     * @param Material $material
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     * @return CatalogProduct
     */
    public function processMaterial(Material $material): CatalogProduct
    {
        return $this->createFromMaterial($material);
    }

    /**
     * @param CatalogProduct $catalogProduct
     *
     * @return bool
     */
    public function updateOrCreate(CatalogProduct $catalogProduct): bool
    {
        return $this->catalogProductRepository->createOrUpdate($catalogProduct);
    }

    /**
     * @param Material $material
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     * @return CatalogProduct
     */
    protected function createFromMaterial(Material $material): CatalogProduct
    {
        $catalogProduct = new CatalogProduct();
        $basicUom = $material->getBasicUnitOfMeasure();
        $result = $catalogProduct
            ->setWidth($basicUom->getWidth() * 1000)
            ->setHeight($basicUom->getHeight() * 1000)
            ->setLength($basicUom->getLength() * 1000)
            ->setWeight($basicUom->getGrossWeight() * 1000);

        if (null !== $this->defaultVatId) {
            $result->setVatId($this->defaultVatId);
        }

        return $result;
    }
}
