<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\Type\Date;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\MobileApiBundle\Dto\Object\ClientCard;
use FourPaws\MobileApiBundle\Dto\Object\User;
use FourPaws\MobileApiBundle\Dto\Request\LoginExistRequest;
use FourPaws\MobileApiBundle\Dto\Request\LoginRequest;
use FourPaws\MobileApiBundle\Dto\Request\PostUserInfoRequest;
use FourPaws\MobileApiBundle\Dto\Response\PostUserInfoResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserLoginResponse;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\UserBundle\Service\UserService as UserBundleService;

class UserService
{
    /**
     * @var UserBundleService
     */
    private $userBundleService;

    public function __construct(UserBundleService $userBundleService)
    {
        $this->userBundleService = $userBundleService;
    }

    /**
     * @param LoginRequest $loginRequest
     *
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \FourPaws\UserBundle\Exception\UsernameNotFoundException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\TooManyUserFoundException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\UserBundle\Exception\InvalidCredentialException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @return UserLoginResponse
     */
    public function login(LoginRequest $loginRequest)
    {
        $this->userBundleService->login($loginRequest->getLogin(), $loginRequest->getPassword());
        return new UserLoginResponse($this->getCurrentApiUser());
    }

    /**
     * @throws \FourPaws\MobileApiBundle\Exception\RuntimeException
     */
    public function logout()
    {
        if (!$this->userBundleService->logout()) {
            throw new RuntimeException('Cant logout user');
        }
        return [
            'feedback_text' => 'Вы вышли из своей учетной записи',
        ];
    }

    /**
     * @param PostUserInfoRequest $userInfoRequest
     *
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @return PostUserInfoResponse
     */
    public function update(PostUserInfoRequest $userInfoRequest)
    {
        $fromRequestUser = $userInfoRequest->getUser();
        $user = $this->userBundleService->getCurrentUser();
        if ($fromRequestUser->getEmail() && $user->getEmail() === $user->getLogin()) {
            $user->setLogin($fromRequestUser->getEmail());
        } elseif ($fromRequestUser->getPhone() && $user->getPersonalPhone() === $user->getLogin()) {
            $user->setLogin($fromRequestUser->getPhone());
        }
        $user
            ->setEmail($fromRequestUser->getEmail() ?? $user->getEmail())
            ->setPersonalPhone($fromRequestUser->getPhone() ?? $user->getPersonalPhone())
            ->setName($fromRequestUser->getFirstName() ?? $user->getName())
            ->setLastName($fromRequestUser->getLastName() ?? $user->getLastName())
            ->setSecondName($fromRequestUser->getMidName() ?? $user->getSecondName());

        /**
         * @todo А точно ли в строку превратится? Возможно нужен свой тип
         */
        if ('' === $fromRequestUser->getBirthDate()) {
            $user->setBirthday(null);
        } elseif (null !== $fromRequestUser->getBirthDate()) {
            $user->setBirthday(Date::createFromPhp($fromRequestUser->getBirthDate()));
        }
        $this->userBundleService->getUserRepository()->update($user);
        return new PostUserInfoResponse($this->getCurrentApiUser());
    }

    /**
     * @param LoginExistRequest $existRequest
     *
     * @throws \FourPaws\UserBundle\Exception\TooManyUserFoundException
     * @return array
     */
    public function isExist(LoginExistRequest $existRequest)
    {
        $exist = $this->userBundleService->getUserRepository()->isExist($existRequest->getLogin());
        /**
         * @todo Необходимо предусмотреть максимальное кол-во попыток
         */

        return [
            'exist'         => $exist,
            'feedback_text' => $exist ? '' : 'Проверьте правильность заполнения поля. Введите ваш E-mail или номер телефона',
        ];
    }

    /**
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @return User
     */
    protected function getCurrentApiUser()
    {
        $user = $this->userBundleService->getCurrentUser();
        $apiUser = new User();
        $apiUser
            ->setEmail($user->getEmail())
            ->setFirstName($user->getName())
            ->setLastName($user->getLastName())
            ->setMidName($user->getSecondName())
            ->setPhone($user->getPersonalPhone())
            ->setCard($this->getCard($user->getId()));
        if ($user->getBirthday()) {
            $apiUser->setBirthDate(\DateTime::createFromFormat('d.m.Y', $user->getBirthday()->format('d.m.Y')));
        }
        return $apiUser;
    }

    protected function getCard(int $userId)
    {
        // ToDo: Сделать реальное получение карты
        return (new ClientCard())->setTitle('Карта клиента')
            ->setPicture(new FullHrefDecorator('/upload/card/img.png'))
            ->setBalance(1500)
            ->setNumber('000011112222')
            ->setBarCode('60832513')
            ->setSaleAmount(3);
    }
}
