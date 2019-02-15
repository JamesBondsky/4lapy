<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressCreateRequest;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressDeleteRequest;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressGetRequest;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressUpdateRequest;
use FourPaws\MobileApiBundle\Dto\Response\DeliveryAddressGetResponse;
use FourPaws\MobileApiBundle\Dto\Response\FeedbackResponse;
use FourPaws\MobileApiBundle\Exception\DeliveryAddressAddError;
use FourPaws\MobileApiBundle\Services\Api\UserDeliveryAddressService as ApiUserDeliveryAddressService;
use FourPaws\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class UserDeliveryController
 * @package FourPaws\MobileApiBundle\Controller\v0
 */
class DeliveryAddressController extends FOSRestController
{
    /**
     * @var ApiUserDeliveryAddressService
     */
    private $apiUserDeliveryAddressService;

    public function __construct(ApiUserDeliveryAddressService $apiUserDeliveryAddressService)
    {
        $this->apiUserDeliveryAddressService = $apiUserDeliveryAddressService;
    }

    /**
     * @Security("has_role('REGISTERED_USERS')")
     * @Rest\Get("/delivery_address/")
     * @Rest\View()
     *
     * @param DeliveryAddressGetRequest $deliveryAddressGetRequest
     * @return DeliveryAddressGetResponse
     */
    public function listAction(DeliveryAddressGetRequest $deliveryAddressGetRequest): DeliveryAddressGetResponse
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        return new DeliveryAddressGetResponse(
            $this->apiUserDeliveryAddressService->getList($user->getId(), $deliveryAddressGetRequest->getCityCode())
        );
    }

    /**
     * @Rest\Put("/delivery_address/")
     * @Rest\View(serializerGroups={"Default", "create"})
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param DeliveryAddressCreateRequest $request
     * @throws \LogicException
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @throws DeliveryAddressAddError
     * @return FeedbackResponse
     */
    public function createAction(DeliveryAddressCreateRequest $request): FeedbackResponse
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $this->apiUserDeliveryAddressService->add($user->getId(), $request->getAddress());

        return new FeedbackResponse('Адрес доставки успешно добавлен');
    }

    /**
     * @Rest\Post("/delivery_address/")
     * @Rest\View(serializerGroups={"Default", "update"})
     * @Security("has_role('REGISTERED_USERS')")
     * @param DeliveryAddressUpdateRequest $addressUpdateRequest
     * @throws \LogicException
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @throws \FourPaws\MobileApiBundle\Exception\HackerException
     * @throws \FourPaws\MobileApiBundle\Exception\DeliveryAddressUpdateError
     * @return FeedbackResponse
     */
    public function updateAction(DeliveryAddressUpdateRequest $addressUpdateRequest): FeedbackResponse
    {
        $user = $this->getUser();
        $this->apiUserDeliveryAddressService->update($user->getId(), $addressUpdateRequest->getAddress());

        return new FeedbackResponse('Адрес доставки успешно обновлен');
    }

    /**
     * @Rest\Delete("/delivery_address/")
     * @Rest\View(serializerGroups={"Default", "delete"})
     * @Security("has_role('REGISTERED_USERS')")
     * @param DeliveryAddressDeleteRequest $addressDeleteRequest
     * @return FeedbackResponse
     */
    public function removeAction(DeliveryAddressDeleteRequest $addressDeleteRequest): FeedbackResponse
    {
        $user = $this->getUser();
        $this->apiUserDeliveryAddressService->delete($user->getId(), $addressDeleteRequest->getId());

        return new FeedbackResponse('Адрес доставки успешно удален');
    }
}
