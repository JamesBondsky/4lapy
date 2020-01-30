<?php

namespace FourPaws\DeliveryBundle\AjaxController;


use DateTime;
use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\SaleBundle\Service\BasketService;
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
     * @var BasketService
     */
    protected $basketService;

    /**
     * @param LocationService $locationService
     * @param DeliveryService $deliveryService
     * @param BasketService $basketService
     */
    public function __construct(LocationService $locationService, DeliveryService $deliveryService, BasketService $basketService)
    {
        $this->locationService = $locationService;
        $this->deliveryService = $deliveryService;
        $this->basketService = $basketService;
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

    /**
     * @Route("/cache/hot/", methods={"GET"})
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function hotCache(): JsonResponse
    {
        $from = new DateTime();

        $basket = $this->basketService->getBasket();

        $locationCode = $this->locationService->getCurrentLocation();

        $shipment = $this->deliveryService->generateShipment($locationCode, $basket);

        $start = microtime(true);
        $this->deliveryService->calculateDeliveries($shipment, [], $from);
        $timeExec = (microtime(true) - $start);

        return JsonSuccessResponse::createWithData('', [
            'status' => true,
            'time' => $timeExec
        ]);
    }
}
