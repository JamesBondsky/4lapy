<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryTime;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryTimeAvailable;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryRangeRequest;
use FourPaws\MobileApiBundle\Dto\Response\DeliveryRangeResponse;

/**
 * Class DeliveryController
 * @package FourPaws\MobileApiBundle\Controller\v0
 */
class DeliveryController extends FOSRestController
{
    /**
     * @var DeliveryService
     */
    private $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    /**
     * @Rest\Get("/delivery_range2/")
     * @Rest\View()
     *
     * @param DeliveryRangeRequest $deliveryRangeRequest
     * @return DeliveryRangeResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \Bitrix\Main\SystemException
     */
    public function getDeliveryRangeAction(DeliveryRangeRequest $deliveryRangeRequest): DeliveryRangeResponse
    {
        $locationCode = $deliveryRangeRequest->getCityId();
        $deliveryResults = $this->deliveryService->getByLocation($locationCode);
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
                        ->setAvailable((new DeliveryTimeAvailable($deliveryResult->getDeliveryDate())));
                }
            }
        }

        return (new DeliveryRangeResponse())->setRanges($ranges);
    }
}
