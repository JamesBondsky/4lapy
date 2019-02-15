<?php

/**
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\Date;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\MobileApiBundle\Dto\Object\City;
use FourPaws\MobileApiBundle\Dto\Object\ClientCard;
use FourPaws\MobileApiBundle\Dto\Object\User;
use FourPaws\MobileApiBundle\Dto\Request\LoginRequest;
use FourPaws\MobileApiBundle\Dto\Response\PostUserInfoResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserLoginResponse;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Exception\TokenNotFoundException;
use FourPaws\MobileApiBundle\Security\ApiToken;
use FourPaws\MobileApiBundle\Services\Session\SessionHandler;
use FourPaws\UserBundle\Entity\User as AppUser;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\UserService as UserBundleService;
use FourPaws\MobileApiBundle\Services\Api\CaptchaService as ApiCaptchaService;
use FourPaws\External\ManzanaService as AppManzanaService;
use FourPaws\MobileApiBundle\Dto\Object\PersonalBonus;
use FourPaws\PersonalBundle\Entity\CardBonus;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\SessionUnavailableException;
use FourPaws\MobileApiBundle\Services\Api\CityService as ApiCityService;

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

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ApiCityService
     */
    private $apiCityService;

    public function __construct(
        UserBundleService $userBundleService,
        UserRepository $userRepository,
        ApiCaptchaService $apiCaptchaService,
        SessionHandler $sessionHandler,
        AppManzanaService $appManzanaService,
        TokenStorageInterface $tokenStorage,
        ApiCityService $apiCityService
    )
    {
        $this->userBundleService = $userBundleService;
        $this->userRepository = $userRepository;
        $this->apiCaptchaService = $apiCaptchaService;
        $this->sessionHandler = $sessionHandler;
        $this->appManzanaService = $appManzanaService;
        $this->tokenStorage = $tokenStorage;
        $this->apiCityService = $apiCityService;
    }

    /**
     * @param LoginRequest $loginRequest
     * @return UserLoginResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     */
    public function loginOrRegister(LoginRequest $loginRequest): UserLoginResponse
    {

        $exlcudePhonesFromCaptchaCheck = [
            '9778016362',
            '9660949453',
            '9299821844',
            '9007531672',
            '9007523221',
            '9991693811',
            '9263987654',
            '9653770455',
            '9165919854'
        ];

        try {

            $_COOKIE[ConfirmCodeService::getCookieName('phone')] = $loginRequest->getCaptchaId();

            if (!in_array($loginRequest->getLogin(), $exlcudePhonesFromCaptchaCheck)) {
                if (!ConfirmCodeService::checkCode($loginRequest->getCaptchaValue(), 'phone')) {
                    throw new RuntimeException('Некорректный код');
                }
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
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
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
            ->setSecondName($user->getMidName() ?? $currentUser->getSecondName())
            ->setLocation($user->getLocationId() ?? $currentUser->getLocation());

        if ('' === $user->getBirthDate()) {
            $currentUser->setBirthday(null);
        } elseif (null !== $user->getBirthDate()) {
            try {
                $currentUser->setBirthday(new Date($user->getBirthDate(), 'd.m.Y'));
            } catch (ObjectException $e) {
            }
        }
        if ($user->getCard()) {
            $currentUser->setDiscountCardNumber($user->getCard()->getNumber());
        }
        $this->userBundleService->getUserRepository()->update($currentUser);
        return new PostUserInfoResponse($this->getCurrentApiUser());
    }

    /**
     * @param string $locationId
     * @return bool
     */
    public function updateLocationId(string $locationId)
    {
        $currentUser = $this->userBundleService->getCurrentUser()->setLocation($locationId);
        return $this->userBundleService->getUserRepository()->update($currentUser);
    }

    /**
     * @param string $login
     *
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function doesExist(string $login): bool
    {
        /**
         * @todo Необходимо предусмотреть максимальное кол-во попыток
         */
        return $this->userRepository->doesExist($login);
    }

    /**
     * Берем данные о пользователе из переданного токена
     * @return User
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    public function getCurrentApiUser(): User
    {
        /**
         * @var ApiToken $token | null
         */
        if (!$token = $this->tokenStorage->getToken()) {
            throw new TokenNotFoundException();
        }
        if (!$session = $token->getApiUserSession()) {
            throw new SessionUnavailableException();
        }
        $user = $this->userRepository->find($session->getUserId());
        ;
        $apiUser = new User();
        $apiUser
            ->setId($user->getId())
            ->setEmail($user->getEmail())
            ->setFirstName($user->getName())
            ->setLastName($user->getLastName())
            ->setMidName($user->getSecondName())
            ->setPhone($user->getPersonalPhone())
            ->setCard($this->getCard($user))
        ;
        if ($user->getBirthday()) {
            $apiUser->setBirthDate($user->getBirthday()->format('d.m.Y'));
        }
        if ($userLocation = $this->getLocation($user)) {
            $apiUser
                ->setLocation($userLocation)
                ->setLocationId($userLocation->getId());
        }
        return $apiUser;
    }

    /**
     * @param AppUser $user
     * @return ClientCard|null
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    protected function getCard(AppUser $user)
    {
        if (!$user->getDiscountCardNumber()) {
            return null;
        }
        try {
            $card = $this->appManzanaService->searchCardByNumber($user->getDiscountCardNumber());
        } catch (CardNotFoundException $exception) {
            // return null;
            // получаем баланс из аккаунта битрикса
        }
        return (new ClientCard())
            ->setTitle('Карта клиента')
            ->setBalance($card->plBalance ?: 0)
            ->setNumber($user->getDiscountCardNumber())
            ->setSaleAmount(3);
    }

    /**
     * @param AppUser $user
     * @return City
     */
    protected function getLocation(AppUser $user)
    {
        return $this->apiCityService->searchByCode($user->getLocation())->current();
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
     * @throws CardNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    public function getPersonalBonus()
    {
        $user = $this->getCurrentApiUser();
        $card = $this->appManzanaService->searchCardByNumber($user->getCard()->getNumber());
        $cardBonus = (new CardBonus());
        $cardBonus->setSumDiscounted($card->plDiscountSumm ?? 0);

        return (new PersonalBonus())
            ->setAmount($card->plDiscountSumm ?? 0)
            ->setTotalIncome($card->plDebet ?? 0)
            ->setTotalOutgo($card->plCredit ?? 0)
            ->setNextStage($cardBonus->getSumToNext());
    }
}
