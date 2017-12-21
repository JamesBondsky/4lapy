<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use FourPaws\Catalog\CatalogService;
use FourPaws\Catalog\Model\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class CategoryConverter implements ParamConverterInterface
{
    /**
     * @var CatalogService
     */
    private $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request        $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $path = $request->get('path');
        $this->catalogService->getCategory($request, $path);
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration): bool
    {
        return Category::class === $configuration->getClass();
    }
}
