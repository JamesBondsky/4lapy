<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Request\UserLoginRequest;
use FourPaws\MobileApiBundle\Services\ApiRequestProcessor;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;
use Symfony\Component\HttpFoundation\Request;

class UserController extends FOSRestController
{
    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthorization;

    /**
     * @var ApiRequestProcessor
     */
    private $apiRequestProcessor;

    public function __construct(UserAuthorizationInterface $userAuthorization, ApiRequestProcessor $apiRequestProcessor)
    {
        $this->userAuthorization = $userAuthorization;
        $this->apiRequestProcessor = $apiRequestProcessor;
    }

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
     * @return \FourPaws\MobileApiBundle\Dto\Response
     */
    public function loginAction(Request $request)
    {
        $result = new \FourPaws\MobileApiBundle\Dto\Response();


        if ($this->userAuthorization->isAuthorized()) {
            /**
             * @todo change code
             */
            $result->addError(new Error(1, 'Вы уже авторизованы'));
            return $result;
        }


        $userLoginRequest = $this->apiRequestProcessor->convert($request->request->all(), UserLoginRequest::class);
        $validateResult = $this->apiRequestProcessor->validate($userLoginRequest);
        if ($validateResult->count() === 0) {
            /**
             * @todo check exists
             */

            /**
             * @todo login
             */

            /**
             * @todo register
             */

            /**
             * @todo session update
             */
        }


        /**
         * todo add error result
         */

        return $result;
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
