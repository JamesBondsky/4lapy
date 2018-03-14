<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressDeleteRequest;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressPostPutRequest;
use FourPaws\MobileApiBundle\Dto\Response\DeliveryAddressGetResponse;
use FourPaws\MobileApiBundle\Dto\Response\FeedbackResponse;

class UserDeliveryController extends FOSRestController
{
    /**
     * @Rest\Get("/delivery_address")
     * @see DeliveryAddressGetResponse
     */
    public function getAction()
    {
        /**
         * @todo Проверяем авторизацию
         */

        /**
         * @todo Получаем адреса
         */

        /**
         * @todo Возвращаем массив адресов
         */
    }

    /**
     * @Rest\Post("/delivery_address")
     * @see DeliveryAddressPostPutRequest
     * @see FeedbackResponse
     * @todo assert
     */
    public function createAction()
    {
    }

    /**
     * @Rest\Put("/delivery_address")
     * @see DeliveryAddressPostPutRequest
     * @see FeedbackResponse
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
