<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Service\DeliveryService as AppDeliveryService;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryTime;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryTimeAvailable;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryRangeRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\DeliveryRangeResponse;
use FourPaws\SaleBundle\Repository\Table\AnimalShelterTable;

/**
 * Class DeliveryController
 * @package FourPaws\MobileApiBundle\Controller\v0
 */
class DeliveryController extends BaseController
{
    /**
     * @var AppDeliveryService
     */
    private $appDeliveryService;

    public function __construct(AppDeliveryService $appDeliveryService)
    {
        $this->appDeliveryService = $appDeliveryService;
    }

    /**
     * @Rest\Get("/delivery_range2/")
     * @Rest\View()
     *
     * @param DeliveryRangeRequest $deliveryRangeRequest
     * @return DeliveryRangeResponse
     * @throws ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws SystemException
     */
    public function getDeliveryRangeAction(DeliveryRangeRequest $deliveryRangeRequest): DeliveryRangeResponse
    {
        $locationCode = $deliveryRangeRequest->getCityId();
        $deliveryResults = $this->appDeliveryService->getByLocation($locationCode);
        $ranges = [];
        /** @var DeliveryResult $deliveryResult */
        foreach ($deliveryResults as $deliveryResult) {
            if ($deliveryResult instanceof DeliveryResult) {
                $dateStmp = FormatDate('d.m.Y', $deliveryResult->getDeliveryDate()->getTimestamp());
                foreach ($deliveryResult->getAvailableIntervals() as $interval) {
                    /** @var Interval $interval */
                    //todo refactor
                    $ranges[] = (new DeliveryTime())
                        ->setId($deliveryResult->getDeliveryDate()->getTimestamp() . $interval->getFrom() . $interval->getTo())
                        ->setTitle($dateStmp . ' ' . $interval->getFrom() . ':00 - ' . $interval->getTo() . ':00')
                        ->setDeliveryDate(new \DateTime($dateStmp))
                        ->setAvailable((new DeliveryTimeAvailable($deliveryResult->getDeliveryDate())))
                        ->setComplete(1);
                }
            }
        }

        return new DeliveryRangeResponse($ranges);
    }

    /**
     * @Rest\Get("/shelters/")
     * @Rest\View()
     *
     * @return Response
     */
    public function getSheltersAction(): Response
    {
        $response = new Response();
        try {
            $shelters = AnimalShelterTable::getList()->fetchAll();
            if (count($shelters)) {
                $shelters = array_map(function ($shelter) {
                    $shelter['id'] = $shelter['barcode'];
                    unset($shelter['barcode']);
                    return $shelter;
                }, $shelters);
                $response->setData(['shelters' => $shelters]);
            } else {
                $response->setData(['shelters' => []]);
            }
        } catch (ObjectPropertyException|ArgumentException|SystemException $e) {
            $response->setData([]);
            $response->addError(new Error(0, $e->getMessage()));
        }

        return $response;
    }
}
