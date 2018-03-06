<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;


use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Filter\ProductIdFilter;
use FourPaws\CatalogBundle\Dto\ProductListRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductListRequestConverter implements ParamConverterInterface
{
    /**
     * @param ParamConverter $configuration
     * @return bool
     */
    public function supports(ParamConverter $configuration): bool
    {
        return ProductListRequest::class === $configuration->getClass();
    }

    /**
     * @param Request $request
     * @param ParamConverter $configuration
     * @return bool
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $productIds = array_filter((array)$request->get('product', []));

        $idFilter = new ProductIdFilter();
        $idFilter->initState($request);

        $filters = new FilterCollection();
        $filters->add($idFilter);

        $productListRequest = (new ProductListRequest())->setProductIds($productIds)->setFilters($filters);

        $request->attributes->set('productListRequest', $productListRequest);

        return true;
    }
}