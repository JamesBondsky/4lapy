<?php

/**
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Enum\UserGroup as UserGroupEnum;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\ExpertsenderService;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\MobileApiBundle\Dto\Object\City;
use FourPaws\MobileApiBundle\Dto\Object\ClientCard;
use FourPaws\MobileApiBundle\Dto\Object\User;
use FourPaws\MobileApiBundle\Dto\Request\LoginRequest;
use FourPaws\MobileApiBundle\Dto\Response\PostUserInfoResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserLoginResponse;
use FourPaws\MobileApiBundle\Exception\EmailAlreadyUsed;
use FourPaws\MobileApiBundle\Exception\InvalidCredentialException;
use FourPaws\MobileApiBundle\Exception\NotFoundUserException;
use FourPaws\MobileApiBundle\Exception\PhoneAlreadyUsed;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Exception\TokenNotFoundException;
use FourPaws\MobileApiBundle\Security\ApiToken;
use FourPaws\MobileApiBundle\Services\Session\SessionHandler;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\UserBundle\Entity\User as AppUser;
use FourPaws\UserBundle\Exception\EmptyPhoneException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Exception\UserException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Repository\GroupRepository;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
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

    /** @var StampService */
    private $stampService;

    public function __construct(
        UserBundleService $userBundleService,
        UserRepository $userRepository,
        ApiCaptchaService $apiCaptchaService,
        SessionHandler $sessionHandler,
        AppManzanaService $appManzanaService,
        TokenStorageInterface $tokenStorage,
        ApiCityService $apiCityService,
        AppBonusService $appBonusService,
        PersonalOrderService $personalOrderService,
        StampService $stampService
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
        $this->stampService = $stampService;
    }

    /**
     * @param LoginRequest $loginRequest
     * @return UserLoginResponse
     * @throws ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws EmptyPhoneException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     */
    public function loginOrRegister(LoginRequest $loginRequest): UserLoginResponse
    {

        $excludeLoginsFromCaptchaCheck = [
            '9778016362',
            '9660949453',
            '9299821844', // Данил
            '9007531672',
            '9007523221',
            '9991693811',
            '9263987654',
            '9653770455',
            '9165919854',
            '9636263044',
            'a.vorobyev@articul.ru',
            '9051552482', // Андрей
            '9255025069',
            '9139740008',
            '9046656072', // Tarox25@gmail.com
            '9600401906',
            '9178445061',
            '9779461734', // Сергей Боканев
            '9683618355',
            '9281448800',
            'm.balezin@articul.ru',
            '9281448800',
            '9167750000',
        ];

        try {

            $_COOKIE[ConfirmCodeService::getCookieName('phone')] = $loginRequest->getCaptchaId();

            if (!in_array($loginRequest->getLogin(), $excludeLoginsFromCaptchaCheck)) {
                try {
                    if (!ConfirmCodeService::checkCode($loginRequest->getCaptchaValue(), 'phone')) {
                        throw new InvalidCredentialException();
                    }
                } catch (NotFoundConfirmedCodeException $e) {
                    throw new InvalidCredentialException();
                }
            }
            $userId = $this->userRepository->findIdentifierByRawLogin($loginRequest->getLogin());
            try {
                if ($this->userBundleService->getCurrentUserId() === $userId) {
                    return new UserLoginResponse($this->getCurrentApiUser());
                }
            } catch (NotAuthorizedException $e) {
            }
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
        try {
            $container = Application::getInstance()->getContainer();

            /** @var ManzanaService $manzanaService */
            $manzanaService = $container->get('manzana.service');

            /**
             * @var UserService $userService
             */
            $userService = $container->get(CurrentUserProviderInterface::class);
            if (!isset($user)) {
                $user = $userService->getUserRepository()->find($userId);
            }

            if ($user === null) {
                throw new UserException('Пользователь не найден');
            }

            $client = new Client();
            if ($_SESSION['MANZANA_CONTACT_ID']) {
                $client->contactId = $_SESSION['MANZANA_CONTACT_ID'];
                unset($_SESSION['MANZANA_CONTACT_ID']);
            }

            if (!$client->contactId) {
                try {
//                    $manzanaContact = $manzanaService->getContactByPhone($user->getManzanaNormalizePersonalPhone());
                    $contactId = $manzanaService->getContactIdByPhone($user->getManzanaNormalizePersonalPhone());
                    $client->contactId = $contactId;
                } catch (ManzanaServiceContactSearchNullException $e) {
                    // Значит, новый пользователь
                } catch (Exception $e) {
                    $logger = LoggerFactory::create('loginOrRegister');
                    $logger->error(sprintf('%s getContactByPhone exception: %s', __METHOD__, $e->getMessage()));
                }
            }

            $userService->setClientPersonalDataByCurUser($client, $user);

            $manzanaService->updateContact($client);

            if ($client->phone && $client->contactId) {
                $manzanaService->updateUserCardByClient($client);
            }
        } catch (Exception $e) {
            $logger = LoggerFactory::create('loginOrRegister');
            $logger->error(sprintf('%s exception: %s', __METHOD__, $e->getMessage()));
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
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws EmptyPhoneException
     */
    public function update(User $user): PostUserInfoResponse
    {
        $currentUser = $this->userBundleService->getCurrentUser();
        $oldUser = clone $currentUser;

        if ($user->getPhone() && $user->getPhone() !== $currentUser->getPersonalPhone()) {
            try {
                if ($userByPhone = $this->userBundleService->findOneByPhone($user->getPhone())) {
                    throw new PhoneAlreadyUsed();
                }
            } catch (NotFoundException $e) {
                // do nothing
            }
        }

        if ($user->getEmail() && $user->getEmail() !== $currentUser->getEmail()) {
            try {
                if ($userByEmail = $this->userBundleService->findOneByEmail($user->getEmail())) {
                    throw new EmailAlreadyUsed();
                }
            } catch (NotFoundException $e) {
                // do nothing
            }
        }

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

        if ($user->getEmail() && $user->getEmail() !== $oldUser->getEmail()) {
            try {
                /** @var ExpertsenderService $expertSenderService */
                $expertSenderService = Application::getInstance()->getContainer()->get('expertsender.service');
                $expertSenderService->sendChangeEmail($oldUser, $currentUser);
            } catch (Exception $exception) {
                $logger = LoggerFactory::create('change_email');
                $logger->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }
        }

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
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
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
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws EmptyPhoneException
     * @throws NotFoundUserException
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

        if ($user === null) {
            throw new NotFoundUserException('Пользоваель не найден');
        }

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
        try {
            $apiUser->setStampsIncome($this->stampService->getActiveStampsCount()); //TODO переделать(?) на вывод значения, сохраненного в профиле пользователя (для этого нужно его заранее асинхронно обновлять)
        } catch (Exception $e) {
            $logger = LoggerFactory::create('getCurrentApiUser');
            $logger->error(sprintf('%s getActiveStampsCount exception: %s', __METHOD__, $e->getMessage()));

            $apiUser->setStampsIncome(0);
        }
        return $apiUser;
    }

    /**
     * @param AppUser $user
     * @return ClientCard|null
     * @throws ApplicationCreateException
     * @throws EmptyPhoneException
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
        }
        return (new ClientCard())
            ->setTitle('Карта клиента')
            ->setBalance(isset($bonusInfo) ? $bonusInfo->getActiveBonus() : $user->getActiveBonus())
            ->setTempIncome(isset($bonusInfo) ? $bonusInfo->getTemporaryBonus() : $user->getTemporaryBonus())
            ->setNumber($user->getDiscountCardNumber())
            ->setSaleAmount($user->getDiscount());
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
     * @throws ArgumentException
     * @throws SystemException
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
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws EmptyPhoneException
     */
    public function getPersonalBonus()
    {
        $logger = LoggerFactory::create('getPersonalBonus');

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
        try {
            $bonusInfo = $this->appBonusService->getManzanaBonusInfo($user);
        } catch (\FourPaws\External\Exception\ManzanaServiceContactSearchNullException $exception) {
            $logger->error(sprintf('%s exception: %s', __METHOD__, $exception->getMessage()));
            return new PersonalBonus();
        } catch (\FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException $exception) {
            $logger->error(sprintf('%s exception: %s', __METHOD__, $exception->getMessage()));
            return new PersonalBonus();
        } catch (\FourPaws\External\Exception\ManzanaServiceException $exception) {
            $logger->error(sprintf('%s exception: %s', __METHOD__, $exception->getMessage()));
        }

        try {
            if (isset($bonusInfo)) {
                $this->userBundleService->refreshUserBonusPercent($user, $bonusInfo);
            }
        } catch (\Exception $e) {
            $logger->error(sprintf('%s exception: %s', __METHOD__, $e->getMessage()));
        }

        return (new PersonalBonus())
            ->setAmount($user->getDiscount() ?? 0)
            ->setTotalIncome(isset($bonusInfo) ? ($bonusInfo->getDebit() ?? 0) : 0)
            ->setTempIncome(isset($bonusInfo) ? ($bonusInfo->getTemporaryBonus() ?? 0) : 0)
            ->setTotalOutgo(isset($bonusInfo) ? ($bonusInfo->getCredit() ?? 0) : 0)
            ->setNextStage(isset($bonusInfo) ? $bonusInfo->getSumToNext() : 0); //FIXME Это временное решение. Нужно сохранять все поля $bonusInfo на сайте и в случае, если манзана не отвечает, возвращать сохраненные значения
    }
    
    public function getDisposableToken()
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
    
        $currentUser = $this->userBundleService->getCurrentUser();
        
        $token = $this->createToken($currentUser->getId());
        
        $currentUser->setDisposableToken($token);
        $this->userBundleService->getUserRepository()->update($currentUser);
        
        return $token;
    }
    
    private function createToken($userId): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        
        for ($i = 0; $i < 30; $i++) {
            $randstring .= $characters[rand(0, strlen($characters))];
        }
        
        $randstring .= $userId;
        
        return $randstring;
    }
}
