<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\CatalogBundle\Dto\ProductDetailRequest;
use FourPaws\CatalogBundle\Service\CategoriesService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductDetailRequestConverter implements ParamConverterInterface
{
    /**
     * @var CategoriesService
     */
    private $categoriesService;

    public function __construct(CategoriesService $categoriesService)
    {
        $this->categoriesService = $categoriesService;
    }


    /**
     * Stores the object in the request.
     *
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @throws IblockNotFoundException
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $elementCode = $request->get('slug', '');
        $sectionPath = $request->get('path', '');

        if ($this->checkProductSectionPath($elementCode, $sectionPath)) {
        }

        dump($variables);
        die();
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
        return $configuration->getClass() === ProductDetailRequest::class;
    }

    /**
     * @param string $slug
     * @param string $path
     *
     * @throws IblockNotFoundException
     * @return bool
     */
    protected function checkProductSectionPath(string $slug, string $path)
    {
        $variables = [
            'SECTION_CODE_PATH' => $path,
            'ELEMENT_CODE'      => $slug,
        ];

        return \CIBlockFindTools::checkElement(
            IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
            $variables
        );
    }
}
