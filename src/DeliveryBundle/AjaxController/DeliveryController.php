<?php

namespace FourPaws\DeliveryBundle\AjaxController;


use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\LocationBundle\LocationService;
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
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @param LocationService $locationService
     * @param DeliveryService $deliveryService
     */
    public function __construct(LocationService $locationService, DeliveryService $deliveryService)
    {
        $this->locationService = $locationService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * @Route("/express-available/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
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
            $locations = $this->locationService->findLocationByExtService(LocationService::OKATO_SERVICE_CODE, $this->locationService->getDadataLocationOkato($address));

            if (!empty($locations)) {
                $location = current($locations);

                $deliveryTime = $this->deliveryService->getExpressDeliveryInterval($location['CODE']);
                $expressAvailable = true;
            }
        } catch (Exception $e) {
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
