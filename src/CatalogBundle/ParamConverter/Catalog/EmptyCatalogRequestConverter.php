<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 31.05.18
 * Time: 11:22
 */

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;


use FourPaws\CatalogBundle\Dto\EmptyCatalogRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class EmptyCatalogRequestConverter extends AbstractCatalogRequestConverter
{

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        return EmptyCatalogRequest::class === $configuration->getClass();
    }

    /**
     * @return EmptyCatalogRequest
     */
    protected function getCatalogRequestObject(): EmptyCatalogRequest
    {
        return new EmptyCatalogRequest();
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     * @param EmptyCatalogRequest               $object
     *
     * @return bool
     */
    protected function configureCustom(Request $request, ParamConverter $configuration, $object)
    {
        return true;
    }
}