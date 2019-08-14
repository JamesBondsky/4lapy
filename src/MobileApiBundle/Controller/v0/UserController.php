<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use CEvent;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\Manzana\Exception\ExecuteErrorException;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Request\LoginExistRequest;
use FourPaws\MobileApiBundle\Dto\Request\LoginRequest;
use FourPaws\MobileApiBundle\Dto\Request\PostUserInfoRequest;
use FourPaws\MobileApiBundle\Dto\Request\VerificationCodeSendByEmailRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;
use FourPaws\PersonalBundle\Service\StampService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;
use FourPaws\MobileApiBundle\Dto\Response\PersonalBonusResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    /**
     * @var ApiUserService
     */
    private $apiUserService;
    /**
     * @var StampService
     */
    protected $stampService;
    /**
     * @var ApiProductService
     */
    private $apiProductService;

    public function __construct(ApiUserService $apiUserService, StampService $stampService, ApiProductService $apiProductService)
    {
        $this->apiUserService = $apiUserService;
        $this->stampService = $stampService;
        $this->apiProductService = $apiProductService;
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
     *
     * @param LoginRequest $loginRequest
     * @return ApiResponse
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
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
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
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
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
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
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
     */
    public function getPersonalBonusAction(): PersonalBonusResponse
    {
        return (new PersonalBonusResponse())
            ->setPersonalBonus($this->apiUserService->getPersonalBonus());
    }

    /**
     * @Rest\Post(path="/email_code/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param VerificationCodeSendByEmailRequest $verificationCodeRequest
     * @return ApiResponse
     */
    public function postEmailCodeAction(VerificationCodeSendByEmailRequest $verificationCodeRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData([
                'result' => CEvent::SendImmediate(
                    'VerificationCode',
                    's1',
                    [
                        'USER_EMAIL' => $verificationCodeRequest->getEmail(),
                        'CODE' => $verificationCodeRequest->getCode(),
                        'TEXT' => 'Код подтверждения смены адреса электронной почты. Если вы не вносили этих изменений, свяжитесь с нами по телефону +7 (800) 770-00-22',
                    ],
                    'N'
                )
            ]);
    }

    /**
     * @Rest\Get("/stamps/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @return ApiResponse
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\External\Manzana\Exception\ExecuteException
     */
    public function getStampsInfoAction(): ApiResponse //TODO change Response type // см. PersonalBonus для примера
    {
        try {
            $stamps = $this->stampService->getActiveStampsCount();
        } catch (ExecuteErrorException $e) {
            $stamps = 0;
        }

        $exchangeRules = StampService::EXCHANGE_RULES;
        $productsXmlIds = array_keys($exchangeRules);

        $productsListCollection = $this->apiProductService->getListFromXmlIds($productsXmlIds);
        $productsList = $productsListCollection->get(0) ?? [];

        return (new ApiResponse())->setData([
            'stamps' => [
                'amount' => $stamps,
                'description' => "1. Одна марка выдаётся за каждые полные N руб. (400 руб.) в чеке на любые товары, купленные в интернет-магазине, в розничном магазине и в приложении «Четыре Лапы» с предъявлением бонусной карты."
                    . "\n2. Дополнительно могут выдаваться марки за покупку определённых товаров."
                    . "\n3. Марки копятся в Личном кабинете пользователя в отдельном разделе. Марки появляются в Личном Кабинете автоматически после совершенной покупки."
                    . "\n4. Накопленные пользователем марки можно использовать для покупки определённой группы товаров по сниженной цене (для начала будет 4 товара)"
                    . "\n7. Выдача марок осуществляется в определённый период."
                    . "\n8. Количество товара, который можно купить с учётом скидки за электронные марки ограничено."
                    . "\n9. При возврате товара, приобретённого с использованием электронные марок, денежный эквивалент номинала марки не выплачивается, марки восстановлению не подлежат: покупателю возвращается сумма, внесённая денежными средствами в соответствии с данными кассового чека.",
                'goods' => $productsList,
            ],
        ]);
    }
}
