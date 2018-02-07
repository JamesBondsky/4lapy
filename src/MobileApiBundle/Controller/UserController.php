<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;
use Symfony\Component\HttpFoundation\Request;

class UserController extends FOSRestController
{
    /**
     * @Rest\Post(path="/user_login", name="user_login")
     * @Parameter(
     *     name="token",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="identifier token from /start request"
     * )
     * @Response(
     *     response="200"
     * )
     * @Rest\View()
     * @param Request $request
     *
     * @return ApiResponse
     */
    public function loginAction(Request $request): ApiResponse
    {
    }

    /**
     * @Rest\Get(path="/logout")
     */
    public function logoutAction()
    {
        /**
         * @todo logout bitrix
         */

        /**
         * @todo update session - clear session
         */
    }

    /**
     * @Rest\Get(path="/user_info")
     */
    public function getAction()
    {
        /**
         * @todo проверяем авторизован ли пользователь
         */

        /**
         * @todo если авторизован - возвращаем пользователя
         */

        /**
         * @todo если не авторизован возвращаем "user_not_authorized"
         */
    }

    /**
     * @Rest\Post(path="/user_info")
     */
    public function updateAction()
    {
        /**
         * @todo covert $_POST to Dto UserInfoPost
         */

        /**
         * @todo Проверяем авторизованность пользователя
         */

        /**
         * @todo Обновляем логин если он равен телефону или емейлу
         */

        /**
         * @todo Возвращаем результат
         */
    }

    /**
     * @Rest\Get(path="/login_exist")
     * @Parameter(
     *     name="login",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="Phone or Email of user"
     * )
     */
    public function isExistAction()
    {
        /**
         * @todo Необходимо предусмотреть максимальное кол-во попыток
         */

        return [
            'exist'         => true,
            'feedback_text' => '',
        ];
    }
}
