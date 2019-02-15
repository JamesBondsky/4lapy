<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\LoginExistRequest;
use FourPaws\MobileApiBundle\Dto\Request\LoginRequest;
use FourPaws\MobileApiBundle\Dto\Request\PostUserInfoRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;
use FourPaws\MobileApiBundle\Dto\Response\PersonalBonusResponse;

class UserController extends FOSRestController
{
    /**
     * @var ApiUserService
     */
    private $apiUserService;

    public function __construct(ApiUserService $apiUserService)
    {
        $this->apiUserService = $apiUserService;
    }

    /**
     * @Rest\Post(path="/user_login/", name="user_login")
     * @Rest\View()
     *
     * @Parameter(
     *     name="token",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="identifier token from /start/ request"
     * )
     * @Response(
     *     response="200"
     * )
     * @Security("!has_role('REGISTERED_USERS')", message="Вы уже авторизованы")
     *
     * @param LoginRequest $loginRequest
     * @return ApiResponse
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     * @internal param Request $request
     */
    public function loginAction(LoginRequest $loginRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData($this->apiUserService->loginOrRegister($loginRequest));
    }

    /**
     * @Rest\Get(path="/logout/", name="logout")
     * @Rest\View()
     * @Response(
     *     response="200"
     * )
     * @Security("has_role('REGISTERED_USERS')", message="Вы не авторизованы")
     *
     * @throws \FourPaws\MobileApiBundle\Exception\RuntimeException
     */
    public function logoutAction(): ApiResponse
    {
        return (new ApiResponse())
            ->setData($this->apiUserService->logout());
    }

    /**
     * @Rest\Get(path="/user_info/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')", message="Вы не авторизованы")
     *
     * @return ApiResponse
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    public function getUserInfoAction()
    {
        return (new ApiResponse())
            ->setData([
                'user' => $this->apiUserService->getCurrentApiUser(),
            ]);
    }

    /**
     * @Rest\Post(path="/user_info/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param PostUserInfoRequest $userInfoRequest
     * @return ApiResponse
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    public function postUserInfoAction(PostUserInfoRequest $userInfoRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData($this->apiUserService->update($userInfoRequest->getUser()));
    }

    /**
     * @Rest\Get(path="/login_exist/")
     * @Rest\View()
     * @Security("!has_role('REGISTERED_USERS')", message="Вы уже авторизованы")
     * @Parameter(
     *     name="login",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="user phone"
     * )
     *
     * @param LoginExistRequest $loginExistRequest
     * @return ApiResponse
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function loginExistAction(LoginExistRequest $loginExistRequest): ApiResponse
    {
        $doesLoginExist = $this->apiUserService->doesExist($loginExistRequest->getLogin());
        return (new ApiResponse())
            ->setData([
                'exist'         => $doesLoginExist,
                'feedback_text' => $doesLoginExist ? '' : 'Проверьте правильность заполнения поля. Введите ваш E-mail или номер телефона',
            ]);
    }

    /**
     * @Rest\Get("/personal_bonus/")
     * @Rest\View()
     *
     * @return PersonalBonusResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\External\Manzana\Exception\CardNotFoundException
     */
    public function getPersonalBonusAction(): PersonalBonusResponse
    {
        return (new PersonalBonusResponse())
            ->setPersonalBonus($this->apiUserService->getPersonalBonus());
    }
}
