<?php

namespace FourPaws\DeliveryBundle\AjaxController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Exception\DaDataExecuteException;
use FourPaws\LocationBundle\LocationService;
use http\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class InfoController
 * @package FourPaws\UserBundle\Controller
 */
class DeliveryController extends Controller
{
    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * @param LocationService $locationService
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * @Route("/express-available/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getAction(Request $request): JsonResponse
    {
        $address = $request->get('address', '');

        if (empty($address)) {
            return JsonErrorResponse::createWithData('Address is empty');
        }

        $expressAvailable = false;
        $deliveryTime = null;

        try {
            $locations = $this->locationService->findLocationByExtService(LocationService::OKATO_SERVICE_CODE, $this->locationService->getDadataLocationOkato($address,  false));

            if (!empty($locations)) {
                $location = current($locations);

                foreach ($this->locationService->findLocationGroupsById($location['ID']) as $groupCode) {
                    if (in_array($groupCode, DeliveryService::ZONE_EXPRESS_DELIVERY, true)) {
                        $expressAvailable = true;

                        switch ($groupCode) {
                            case DeliveryService::ZONE_EXPRESS_DELIVERY_45:
                                $deliveryTime = '45';
                                break;
                            case DeliveryService::ZONE_EXPRESS_DELIVERY_90:
                                $deliveryTime = '90';
                                break;
                            default:
                                throw new RuntimeException('Delivery zone for express delivery not found');
                                break;
                        }
                    }
                }
            }
        } catch (DaDataExecuteException $e) {
        }

        return JsonSuccessResponse::createWithData(
            '',
            [
                'expressAvailable' => $expressAvailable,
                'deliveryTime' => $deliveryTime,
            ]
        );
    }
}
