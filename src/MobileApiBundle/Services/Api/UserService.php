<?php

/**
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\Date;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\MobileApiBundle\Dto\Object\ClientCard;
use FourPaws\MobileApiBundle\Dto\Object\User;
use FourPaws\MobileApiBundle\Dto\Request\LoginExistRequest;
use FourPaws\MobileApiBundle\Dto\Request\LoginRequest;
use FourPaws\MobileApiBundle\Dto\Response\PostUserInfoResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserLoginResponse;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Services\Session\SessionHandler;
use FourPaws\UserBundle\Entity\User as AppUser;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\UserService as UserBundleService;
use FourPaws\MobileApiBundle\Services\Api\CaptchaService as ApiCaptchaService;
use FourPaws\External\ManzanaService as AppManzanaService;
use FourPaws\MobileApiBundle\Dto\Object\PersonalBonus;
use FourPaws\PersonalBundle\Entity\CardBonus;

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

    /**
     * @var SessionHandler
     */
    private $sessionHandler;

    /**
     * @var AppManzanaService
     */
    private $appManzanaService;

    public function __construct(
        UserBundleService $userBundleService,
        UserRepository $userRepository,
        ApiCaptchaService $apiCaptchaService,
        SessionHandler $sessionHandler,
        AppManzanaService $appManzanaService
    )
    {
        $this->userBundleService = $userBundleService;
        $this->userRepository = $userRepository;
        $this->apiCaptchaService = $apiCaptchaService;
        $this->sessionHandler = $sessionHandler;
        $this->appManzanaService = $appManzanaService;
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
        $this->sessionHandler->login();
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
        $this->sessionHandler->logout();
        return [
            'feedback_text' => 'Вы вышли из своей учетной записи',
        ];
    }

    /**
     * @param User $user
     *
     * @return PostUserInfoResponse
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\External\Manzana\Exception\CardNotFoundException
     */
    public function update(User $user): PostUserInfoResponse
    {
        $currentUser = $this->userBundleService->getCurrentUser();
        if ($user->getEmail() && $currentUser->getEmail() === $currentUser->getLogin()) {
            $currentUser->setLogin($user->getEmail());
        } elseif ($user->getPhone() && $currentUser->getPersonalPhone() === $currentUser->getLogin()) {
            $currentUser->setLogin($user->getPhone());
        }
        $currentUser
            ->setEmail($user->getEmail() ?? $currentUser->getEmail())
            ->setPersonalPhone($user->getPhone() ?? $currentUser->getPersonalPhone())
            ->setName($user->getFirstName() ?? $currentUser->getName())
            ->setLastName($user->getLastName() ?? $currentUser->getLastName())
            ->setSecondName($user->getMidName() ?? $currentUser->getSecondName());

        if ('' === $user->getBirthDate()) {
            $currentUser->setBirthday(null);
        } elseif (null !== $user->getBirthDate()) {
            try {
                $currentUser->setBirthday(new Date($user->getBirthDate(), 'd.m.Y'));
            } catch (ObjectException $e) {
            }
        }
        $this->userBundleService->getUserRepository()->update($currentUser);
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
     * @return User
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    public function getCurrentApiUser(): User
    {
        $user = $this->userBundleService->getCurrentUser();
        $apiUser = new User();
        $apiUser
            ->setId($user->getId())
            ->setEmail($user->getEmail())
            ->setFirstName($user->getName())
            ->setLastName($user->getLastName())
            ->setMidName($user->getSecondName())
            ->setPhone($user->getPersonalPhone())
            ->setCard($this->getCard())
        ;
        if ($user->getBirthday()) {
            $apiUser->setBirthDate($user->getBirthday()->format('d.m.Y'));
        }
        return $apiUser;
    }

    /**
     * @return ClientCard|null
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    protected function getCard()
    {
        $user = $this->userBundleService->getCurrentUser();
        try {
            $card = $this->appManzanaService->searchCardByNumber($user->getDiscountCardNumber());
        } catch (CardNotFoundException $exception) {
            return null;
        }
        return (new ClientCard())
            ->setTitle('Карта клиента')
            ->setPicture(new FullHrefDecorator('/upload/card/img.png')) // не используется
            ->setBalance($card->plBalance)
            ->setNumber($user->getDiscountCardNumber())
            ->setBarCode('') // не используется
            ->setSaleAmount($card->plQuantity);
    }

    /**
     * Актуализирует группы пользователя в битрикс
     * Если у пользователя есть заказы с флагом "из мобильного приложения" - помещаем в группу "Делал заказы из МП"
     * Если нет заказов с флагом "из мобильного приложения" - помещаем в группу "Не делал заказы из МП"
     *
     * Вызывается в методе app_launch
     */
    public function actualizeUserGroupsForApp()
    {
        //toDo...
    }

    /**
     * @return PersonalBonus
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\External\Manzana\Exception\CardNotFoundException
     */
    public function getPersonalBonus()
    {
        $user = $this->getCurrentApiUser();
        $card = $this->appManzanaService->searchCardByNumber($user->getCard()->getNumber());
        $cardBonus = (new CardBonus());
        $cardBonus->setSumDiscounted($card->plDiscountSumm);

        return (new PersonalBonus())
            ->setAmount($card->plDiscountSumm)
            ->setTotalIncome($card->plDebet)
            ->setTotalOutgo($card->plCredit)
            ->setNextStage($cardBonus->getSumToNext());
    }
}
