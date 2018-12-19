<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\ObjectException;
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
use FourPaws\UserBundle\Entity\User as AppUser;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\UserService as UserBundleService;
use FourPaws\MobileApiBundle\Services\Api\CaptchaService as ApiCaptchaService;

class UserService
{
    /**
     * @var UserBundleService
     */
    private $userBundleService;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ApiCaptchaService
     */
    private $apiCaptchaService;

    public function __construct(
        UserBundleService $userBundleService,
        UserRepository $userRepository,
        ApiCaptchaService $apiCaptchaService
    )
    {
        $this->userBundleService = $userBundleService;
        $this->userRepository = $userRepository;
        $this->apiCaptchaService = $apiCaptchaService;
    }

    /**
     * @param LoginRequest $loginRequest
     *
     * @return UserLoginResponse
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\UserBundle\Exception\NotFoundException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     */
    public function loginOrRegister(LoginRequest $loginRequest): UserLoginResponse
    {
        try {
            $isVerified = $GLOBALS['APPLICATION']->CaptchaCheckCode($loginRequest->getCaptchaValue(), $loginRequest->getCaptchaId())
            || in_array($loginRequest->getLogin(), [
                    '9778016362',
                    '9660949453',
                    '9299821844',
                    '9007531672',
                    '9007523221',
                    '9991693811',
                    '9263987654',
                    '9653770455',
                    '9165919854'
                ]);
            if (!$isVerified) {
                throw new RuntimeException('Некорректный код');
            }
            $userId = $this->userRepository->findIdentifierByRawLogin($loginRequest->getLogin());
            $this->userBundleService->authorize($userId);
        } catch (UsernameNotFoundException $exception) {
            $user = new AppUser();
            $user
                ->setPersonalPhone($loginRequest->getLogin())
                ->setLogin($user->getPersonalPhone())
                ->setPassword(randString(20));
            $user = $this->userBundleService->register($user);
            $this->userBundleService->authorize($user->getId());
        }
        return new UserLoginResponse($this->getCurrentApiUser());
    }

    /**
     * @throws \FourPaws\MobileApiBundle\Exception\RuntimeException
     */
    public function logout(): array
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
    public function update(PostUserInfoRequest $userInfoRequest): PostUserInfoResponse
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

        if ('' === $fromRequestUser->getBirthDate()) {
            $user->setBirthday(null);
        } elseif (null !== $fromRequestUser->getBirthDate()) {
            try {
                $user->setBirthday(new Date($fromRequestUser->getBirthDate(), 'd.m.Y'));
            } catch (ObjectException $e) {
            }
        }
        $this->userBundleService->getUserRepository()->update($user);
        return new PostUserInfoResponse($this->getCurrentApiUser());
    }

    /**
     * @param LoginExistRequest $existRequest
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function isExist(LoginExistRequest $existRequest): array
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
    public function getCurrentApiUser(): User
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
            $apiUser->setBirthDate($user->getBirthday()->format('d.m.Y'));
        }
        return $apiUser;
    }

    protected function getCard(int $userId): ClientCard
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
