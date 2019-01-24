<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Dto\Request\PayRequest;
use FourPaws\MobileApiBundle\Dto\Response\PayResponse;
use FourPaws\MobileApiBundle\Services\Api\PaymentService as ApiPaymentService;

/**
 * Class PaymentController
 * @package FourPaws\MobileApiBundle\Controller
 */
class PaymentController extends FOSRestController
{
    /**
     * @var ApiPaymentService
     */
    private $apiPaymentService;

    public function __construct(
        ApiPaymentService $apiPaymentService
    )
    {
        $this->apiPaymentService = $apiPaymentService;
    }

    /**
     * @Rest\Post(path="/pay/")
     * @Rest\View()
     * @param PayRequest $payRequest
     * @return PayResponse
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \Bitrix\Main\SystemException
     */
    public function postPayAction(PayRequest $payRequest): PayResponse
    {
        $url = $this->apiPaymentService->getPaymentUrl(
            $payRequest->getOrderId(),
            $payRequest->getPayType(),
            $payRequest->getPayToken()
        );
        return (new PayResponse())
            ->setFormUrl($url);
    }

}
