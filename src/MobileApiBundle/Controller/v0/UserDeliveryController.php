<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressCreateRequest;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressDeleteRequest;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressUpdateRequest;
use FourPaws\MobileApiBundle\Dto\Response\DeliveryAddressGetResponse;
use FourPaws\MobileApiBundle\Dto\Response\FeedbackResponse;
use FourPaws\MobileApiBundle\Exception\DeliveryAddressAddError;
use FourPaws\MobileApiBundle\Services\Api\UserDeliveryAddressService;
use FourPaws\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class UserDeliveryController
 * @package FourPaws\MobileApiBundle\Controller\v0
 */
class UserDeliveryController extends FOSRestController
{
    /**
     * @var UserDeliveryAddressService
     */
    private $addressService;

    public function __construct(UserDeliveryAddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    /**
     * @Security("has_role('REGISTERED_USERS')")
     * @Rest\Get("/delivery_address/")
     * @Rest\View()
     *
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @throws \LogicException
     * @return DeliveryAddressGetResponse
     *
     */
    public function listAction(): DeliveryAddressGetResponse
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        return new DeliveryAddressGetResponse(
            $this->addressService->getAll($user->getId())
        );
    }

    /**
     * @Rest\Put("/delivery_address/")
     * @Rest\View()
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
        $this->addressService->add($user->getId(), $request->getAddress());

        return new FeedbackResponse('Адрес доставки успешно добавлен');
    }

    /**
     * @Rest\Post("/delivery_address/")
     * @Rest\View()
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
        $this->addressService->update($user->getId(), $addressUpdateRequest->getAddress());

        return new FeedbackResponse('Адрес доставки успешно обновлен');
    }

    /**
     * @Rest\Delete("/delivery_address/")
     * @Rest\View()
     * @param DeliveryAddressDeleteRequest $addressDeleteRequest
     * @return FeedbackResponse
     */
    public function removeAction(DeliveryAddressDeleteRequest $addressDeleteRequest): FeedbackResponse
    {
        $user = $this->getUser();
        $this->addressService->delete($user->getId(), $addressDeleteRequest->getId());

        return new FeedbackResponse('Адрес доставки успешно удален');
    }
}
