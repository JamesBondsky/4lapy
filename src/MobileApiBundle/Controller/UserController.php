<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller;

use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\MobileApiBundle\Dto\Object\ClientCard;
use FourPaws\MobileApiBundle\Dto\Request\LoginRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Service\UserService;
use LogicException;
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
     * @param Collection   $apiErrors
     *
     * @return ApiResponse
     * @internal param Request $request
     *
     * @Security("!has_role('REGISTERED_USERS')")
     *
     * @Rest\View()
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @throws InvalidCredentialException
     * @throws LogicException
     */
    public function loginAction(
        LoginRequest $loginRequest,
        Collection $apiErrors
    ): ApiResponse {
        $response = new ApiResponse();

        if ($apiErrors->count()) {
            $response->setErrors($apiErrors);
        } else {
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
        }

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
