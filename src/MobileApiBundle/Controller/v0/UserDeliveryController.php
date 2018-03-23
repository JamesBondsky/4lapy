<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressDeleteRequest;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressPostPutRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\DeliveryAddressGetResponse;
use FourPaws\MobileApiBundle\Dto\Response\FeedbackResponse;
use FourPaws\MobileApiBundle\Services\Api\UserDeliveryAddressService;
use FourPaws\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
     * @throws \LogicException
     */
    public function listAction()
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        return new Response(
            new DeliveryAddressGetResponse(
                $this->addressService->getAll($user->getId())
            )
        );
    }

    /**
     * @Rest\Post("/delivery_address/")
     * @see  DeliveryAddressPostPutRequest
     * @see  FeedbackResponse
     * @todo assert
     */
    public function createAction()
    {
    }

    /**
     * @Rest\Put("/delivery_address")
     * @see  DeliveryAddressPostPutRequest
     * @see  FeedbackResponse
     * @todo assert
     */
    public function updateAction()
    {
    }

    /**
     * @Rest\Delete("/delivery_address")
     * @see DeliveryAddressDeleteRequest
     * @see FeedbackResponse
     */
    public function deleteAction()
    {
        /**
         * @todo Проверка авторизации
         */

        /**
         * @todo Проверить наличие профиля и владение текущим пользователем
         */

        /**
         * @todo Удалить профиль
         */
    }
}
