<?php
/**
 * Created by PhpStorm.
 * User: pinchuk
 * Date: 12/24/17
 * Time: 6:43 PM
 */

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class RootCategoryConverter implements ParamConverterInterface
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
        $name = 'path';

        if (!$request->attributes->has($name)) {
            return false;
        }

        $variables = [
            'SECTION_CODE_PATH' => $request->attributes->get($name, ''),
        ];

        $result = \CIBlockFindTools::checkSection(
            IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
            $variables
        );
        if ($result) {
            $rootCategoryRequest = (new RootCategoryRequest())
                ->setCategorySlug($variables['SECTION_CODE']);
            $request->attributes->set('rootCategoryRequest', $rootCategoryRequest);
        }
        return $result;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === RootCategoryRequest::class;
    }
}
