<?php

/**
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\Date;
use FourPaws\Enum\UserGroup as UserGroupEnum;
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
use FourPaws\UserBundle\Repository\GroupRepository;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\UserService as UserBundleService;
use FourPaws\MobileApiBundle\Services\Api\CaptchaService as ApiCaptchaService;
use FourPaws\External\ManzanaService as AppManzanaService;
use FourPaws\MobileApiBundle\Dto\Object\PersonalBonus;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\SessionUnavailableException;
use FourPaws\MobileApiBundle\Services\Api\CityService as ApiCityService;
use FourPaws\PersonalBundle\Service\BonusService as AppBonusService;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;

class UserService
{
    /** @var UserBundleService */
    private $userBundleService;

    /** @var UserRepository */
    private $userRepository;

    /** @var ApiCaptchaService */
    private $apiCaptchaService;

    /** @var SessionHandler */
    private $sessionHandler;

    /** @var AppManzanaService */
    private $appManzanaService;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ApiCityService */
    private $apiCityService;

    /** @var AppBonusService */
    private $appBonusService;

    /** @var PersonalOrderService */
    private $personalOrderService;

    public function __construct(
        UserBundleService $userBundleService,
        UserRepository $userRepository,
        ApiCaptchaService $apiCaptchaService,
        SessionHandler $sessionHandler,
        AppManzanaService $appManzanaService,
        TokenStorageInterface $tokenStorage,
        ApiCityService $apiCityService,
        AppBonusService $appBonusService,
        PersonalOrderService $personalOrderService
    )
    {
        $this->userBundleService = $userBundleService;
        $this->userRepository = $userRepository;
        $this->apiCaptchaService = $apiCaptchaService;
        $this->sessionHandler = $sessionHandler;
        $this->appManzanaService = $appManzanaService;
        $this->tokenStorage = $tokenStorage;
        $this->apiCityService = $apiCityService;
        $this->appBonusService = $appBonusService;
        $this->personalOrderService = $personalOrderService;
    }

    /**
     * @param LoginRequest $loginRequest
     * @return UserLoginResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
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
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
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
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
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
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
     */
    protected function getCard(AppUser $user)
    {
        if (!$user->getDiscountCardNumber()) {
            return null;
        }
        $bonusInfo = null;
        try {
            $bonusInfo = $this->appBonusService->getManzanaBonusInfo($user);
        } catch (\FourPaws\External\Exception\ManzanaServiceContactSearchNullException $exception) {
            return null;
        } catch (\FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException $exception) {
            return null;
        } catch (\FourPaws\External\Exception\ManzanaServiceException $exception) {
            return null;
        }
        return (new ClientCard())
            ->setTitle('Карта клиента')
            ->setBalance($bonusInfo->getActiveBonus())
            ->setNumber($user->getDiscountCardNumber())
            ->setSaleAmount($bonusInfo->getGeneratedRealDiscount());
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
     *
     * Актуализирует группы пользователя в битрикс
     * Если у пользователя есть заказы с флагом "из мобильного приложения" - помещаем в группу "Делал заказы из МП"
     * @see UserGroupEnum::HAS_ORDERS_FROM_MOBILE_APP
     * Если нет заказов с флагом "из мобильного приложения" - помещаем в группу "Не делал заказы из МП"
     * @see UserGroupEnum::NO_ORDERS_FROM_MOBILE_APP
     *
     * Вызывается в методе app_launch
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function actualizeUserGroupsForApp()
    {
        $user = $this->userBundleService->getCurrentUser();
        $groupIds = \CUser::GetUserGroup($user->getId());

        if ($this->personalOrderService->isUserHasOrdersFromApp($user)) {
            $newGroupId = GroupRepository::getIdByCode(UserGroupEnum::HAS_ORDERS_FROM_MOBILE_APP);
            $deleteGroupId = GroupRepository::getIdByCode(UserGroupEnum::NO_ORDERS_FROM_MOBILE_APP);
            if ($deleteGroupIdKey = array_search($deleteGroupId, $groupIds)) {
                unset($groupIds[$deleteGroupIdKey]);
            }
        } else {
            $newGroupId = GroupRepository::getIdByCode(UserGroupEnum::NO_ORDERS_FROM_MOBILE_APP);
        }
        $groupIds = array_merge([$newGroupId], $groupIds);
        \CUser::SetUserGroup($user->getId(), $groupIds);
    }

    /**
     * @return PersonalBonus
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
     */
    public function getPersonalBonus()
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
        $bonusInfo = $this->appBonusService->getManzanaBonusInfo($user);

        return (new PersonalBonus())
            ->setAmount($bonusInfo->getSumDiscounted() ?? 0)
            ->setTotalIncome($bonusInfo->getDebit() ?? 0)
            ->setTotalOutgo($bonusInfo->getCredit() ?? 0)
            ->setNextStage($bonusInfo->getSumToNext());
    }
}
