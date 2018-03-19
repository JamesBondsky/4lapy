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
     * @param LoginRequest $loginRequest
     *
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \Bitrix\Main\DB\SqlQueryException
     * @return ApiResponse
     * @internal param Request $request
     *
     * @Security("!has_role('REGISTERED_USERS')")
     *
     * @Rest\View()
     */
    public function loginAction(LoginRequest $loginRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData($this->apiUserService->loginOrRegister($loginRequest));
    }

    /**
     * @Rest\Get(path="/logout/", name="logout")
     * @Response(
     *     response="200"
     * )
     *
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @Rest\View()
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
     * @Security("has_role('REGISTERED_USERS')")
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
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
     * @param PostUserInfoRequest $userInfoRequest
     *
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @return ApiResponse
     */
    public function postUserInfoAction(PostUserInfoRequest $userInfoRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData($this->apiUserService->update($userInfoRequest));
    }

    /**
     * @Rest\Get(path="/login_exist/")
     * @Rest\View()
     * @Security("!has_role('REGISTERED_USERS')")
     * @Parameter(
     *     name="login",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="Phone or Email of user"
     * )
     * @param LoginExistRequest $existRequest
     *
     * @throws \FourPaws\UserBundle\Exception\TooManyUserFoundException
     * @return ApiResponse
     */
    public function isExistAction(LoginExistRequest $existRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData($this->apiUserService->isExist($existRequest));
    }
}
