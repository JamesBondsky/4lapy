<?php

namespace FourPaws\SapBundle\Repository;

use FourPaws\BitrixOrm\Model\CatalogProduct;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;

class CatalogProductRepository
{
    /**
     * @var \CCatalogProduct
     */
    protected $catalogProductService;
    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;

    public function __construct(ArrayTransformerInterface $arrayTransformer)
    {
        $this->catalogProductService = new \CCatalogProduct();
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @param CatalogProduct $catalogProduct
     *
     * @return bool
     */
    public function createOrUpdate(CatalogProduct $catalogProduct): bool
    {
        return $this->catalogProductService->Add($this->arrayTransformer->toArray(
            $catalogProduct,
            SerializationContext::create()->setGroups(['create'])
        ));
    }
}
