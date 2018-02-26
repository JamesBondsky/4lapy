<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\MobileApiBundle\Dto\Object\ClientCard;
use FourPaws\MobileApiBundle\Dto\Request\LoginExistRequest;
use FourPaws\MobileApiBundle\Dto\Request\LoginRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;

class UserController extends FOSRestController
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @return ApiResponse
     * @internal param Request $request
     *
     * @Security("!has_role('REGISTERED_USERS')")
     *
     * @Rest\View()
     */
    public function loginAction(
        LoginRequest $loginRequest
    ): ApiResponse {
        $response = new ApiResponse();

        $this->userService->login($loginRequest->getLogin(), $loginRequest->getPassword());

        /**
         * @var User $user
         */
        $user = $this->getUser();

        // ToDo: Сделать реальное получение карты
        $card = (new ClientCard())->setTitle('Карта клиента')
            ->setPicture(new FullHrefDecorator('/upload/card/img.png'))
            ->setBalance(1500)
            ->setNumber('000011112222')
            ->setBarCode('60832513')
            ->setSaleAmount(3);

        $response->setData([
            'email'     => $user->getEmail(),
            'firstname' => $user->getName(),
            'lastname'  => $user->getLastName(),
            'midname'   => $user->getSecondName() ?: '',
            'birthdate' => $user->getBirthday() ? $user->getBirthday()->format('d.m.Y') : '',
            'phone'     => $user->getNormalizePersonalPhone(),
            'card'      => $card,
        ]);

        return $response;
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
     */
    public function logoutAction(): ApiResponse
    {
        $response = new ApiResponse();

        $this->userService->logout();

        $response->setData([
            'feedback_text' => 'Вы вышли из своей учетной записи',
        ]);

        return $response;
    }

    /**
     * @Rest\Get(path="/check/")
     * @Rest\View()
     */
    public function checkAction()
    {
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
     * @Rest\Get(path="/login_exist/")
     * @Rest\View()
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
    public function isExistAction(LoginExistRequest $existRequest)
    {
        $exist = $this->userService->getUserRepository()->isExist($existRequest->getLogin());
        /**
         * @todo Необходимо предусмотреть максимальное кол-во попыток
         */

        return (new ApiResponse())->setData([
            'exist'         => $exist,
            'feedback_text' => $exist ? '' : 'Проверьте правильность заполнения поля. Введите ваш E-mail или номер телефона',
        ]);
    }
}
