<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Request\UserLoginRequest;
use FourPaws\MobileApiBundle\Exception\AlreadyAuthorizedException;
use FourPaws\MobileApiBundle\Exception\InvalidCredentialException;
use FourPaws\MobileApiBundle\Exception\LogicException;
use FourPaws\MobileApiBundle\Exception\SystemException;
use FourPaws\MobileApiBundle\Services\Api\UserService;
use FourPaws\MobileApiBundle\Services\ApiRequestProcessor;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;
use Symfony\Component\HttpFoundation\Request;

class UserController extends FOSRestController
{
    /**
     * @var ApiRequestProcessor
     */
    private $apiRequestProcessor;
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(ApiRequestProcessor $apiRequestProcessor, UserService $userService)
    {
        $this->apiRequestProcessor = $apiRequestProcessor;
        $this->userService = $userService;
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
        /**
         * @var UserLoginRequest $userLoginRequest
         */
        $userLoginRequest = $this->apiRequestProcessor->convert($request->request->all(), UserLoginRequest::class);
        $validateResult = $this->apiRequestProcessor->validate($userLoginRequest);
        if ($validateResult->count() === 0) {
            try {

//            $this->userService->loginOrRegister($userLoginRequest);
                $result->setData($this->userService->loginOrRegister($userLoginRequest));
                return $result;
            } catch (InvalidCredentialException $exception) {
                $result->addError(new Error(1, 'Не верные данные для авторизации'));
            } catch (AlreadyAuthorizedException $exception) {
                $result->addError(new Error(2, 'Вы уже авторизованы'));
            } catch (SystemException $exception) {
                $result->addError(new Error(3, 'Системная ошибка'));
            } catch (LogicException $exception) {
                $result->addError(new Error(4, 'Системная ошибка'));
            }
        } else {
            $result->addError(new Error(5, 'Не корректные данные для авторизации'));
        }


        return $result;

        /**
         * todo add error result
         */
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
