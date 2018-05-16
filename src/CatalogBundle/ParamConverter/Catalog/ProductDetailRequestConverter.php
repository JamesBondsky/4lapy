<?php

namespace FourPaws\CatalogBundle\ParamConverter\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\CatalogBundle\Dto\ProductDetailRequest;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductDetailRequestConverter
 *
 * @package FourPaws\CatalogBundle\ParamConverter\Catalog
 */
class ProductDetailRequestConverter implements ParamConverterInterface
{
    /**
     * @var CurrentUserProviderInterface
     */
    private $deliveryService;

    /**
     * ProductDetailRequestConverter constructor.
     *
     * @param DeliveryService $deliveryService
     */
    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @throws ApplicationCreateException
     * @throws IblockNotFoundException
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $variables = [
            'SECTION_CODE_PATH' => $request->get('path', ''),
            'ELEMENT_CODE' => $request->get('slug', ''),
            'OFFER_ID' => $request->get('offer', 0),
        ];

        $result = \CIBlockFindTools::checkElement(
            IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
            $variables
        );

        if ($result) {
            $productDetailRequest = (new ProductDetailRequest())->setOfferId($variables['OFFER_ID'])
                ->setZone($this->deliveryService->getCurrentDeliveryZone())
                ->setProductSlug($variables['ELEMENT_CODE'])
                ->setSectionSlug($variables['SECTION_CODE']);
            $request->attributes->set('productDetailRequest', $productDetailRequest);

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
        return $configuration->getClass() === ProductDetailRequest::class;
    }
}
