<?php

namespace FourPaws\MobileApiBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\MobileApiBundle\Dto\Request\CategoryRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class CategoryRequestConverter implements ParamConverterInterface
{
    /**
     * Stores the object in the request.
     *
     * @param Request        $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @throws IblockNotFoundException
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if ($request->get('id')) {
            $CategoryRequest = (new CategoryRequest())->setId($request->get('id'));
            $request->attributes->set('CategoryRequest', $CategoryRequest);
            return true;
        }

        return false;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === CategoryRequest::class;
    }
}

?>