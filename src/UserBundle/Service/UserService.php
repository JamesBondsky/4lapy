<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Fuser;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\UserBonus;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\AvatarSelfAuthorizationException;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\RuntimeException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class UserService
 *
 * @package FourPaws\UserBundle\Service
 */
class UserService implements
    CurrentUserProviderInterface,
    UserAuthorizationInterface,
    UserRegistrationProviderInterface,
    UserCitySelectInterface,
    UserAvatarAuthorizationInterface
{
    public const BASE_DISCOUNT = 3;
    /**
     * @var \CAllUser|\CUser
     */
    private $bitrixUserService;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * UserService constructor.
     *
     * @param UserRepository  $userRepository
     * @param LocationService $locationService
     */
    public function __construct(
        UserRepository $userRepository,
        LocationService $locationService
    ) {
        /**
         * todo move to factory service
         */
        global $USER;
        $this->bitrixUserService = $USER;
        $this->userRepository = $userRepository;
        $this->locationService = $locationService;
    }

    /**
     * @param string $rawLogin
     * @param string $password
     *
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @throws InvalidCredentialException
     * @throws WrongPhoneNumberException
     * @return bool
     */
    public function login(string $rawLogin, string $password): bool
    {
        $login = $this->userRepository->findLoginByRawLogin($rawLogin);
        $result = $this->bitrixUserService->Login($login, $password);
        if ($result === true) {
            return true;
        }

        throw new InvalidCredentialException($result['MESSAGE']);
    }

    /**
     * @return bool
     */
    public function logout(): bool
    {
        $this->bitrixUserService->Logout();

        return !$this->isAuthorized();
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return $this->bitrixUserService->IsAuthorized();
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function authorize(int $id): bool
    {
        $this->bitrixUserService->Authorize($id);

        return $this->isAuthorized();
    }

    /**
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->userRepository->find($this->getCurrentUserId());
    }

    /**
     * @return int
     */
    public function getCurrentFUserId(): int
    {
        return (int)Fuser::getId();
    }

    /**
     * @throws NotAuthorizedException
     * @return int
     */
    public function getCurrentUserId(): int
    {
        $id = (int)$this->bitrixUserService->GetID();
        if ($id > 0) {
            return $id;
        }
        throw new NotAuthorizedException('Trying to get user id without authorization');
    }

    /**
     *
     * @param User $user
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws RuntimeException
     * @throws BitrixRuntimeException
     * @throws ValidationException
     * @throws SqlQueryException
     * @throws SystemException
     * @return User
     */
    public function register(User $user): User
    {
        $validationResult = $this->userRepository->getValidator()->validate($user, null, ['create']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to create');
        }

        Application::getConnection()->startTransaction();

        $session = $_SESSION;
        try {
            $_SESSION['SEND_REGISTER_EMAIL'] = true;
            /** регистрируем битровым методом регистрации*/
            $result = $this->bitrixUserService->Register(
                $user->getLogin() ?? $user->getEmail(),
                $user->getName() ?? '',
                $user->getLastName() ?? '',
                $user->getPassword(),
                $user->getPassword(),
                $user->getEmail()
            );
            /** отправка письма происходи на событие after в этот момент */
        } catch (\Exception $e) {
            Application::getConnection()->rollbackTransaction();
            $_SESSION = $session;
            throw new BitrixRuntimeException($e->getMessage(), $e->getCode());
        }

        $result['ID'] = $result['ID'] ?? '';
        $id = (int)$result['ID'];

        if ($id <= 0) {
            Application::getConnection()->rollbackTransaction();
            $_SESSION = $session;
            throw new BitrixRuntimeException($this->bitrixUserService->LAST_ERROR);
        }

        $user
            ->setId($id)
            ->setActive(true);
        if (!$this->userRepository->update($user)) {
            Application::getConnection()->rollbackTransaction();
            $_SESSION = $session;
            throw new RuntimeException('Cant update registred user');
        }

        $registeredUser = $this->userRepository->find($id);
        if (!($registeredUser instanceof User)) {
            Application::getConnection()->rollbackTransaction();
            $_SESSION = $session;
            throw new RuntimeException('Cant fetch registred user');
        }
        Application::getConnection()->commitTransaction();

        return $registeredUser;
    }

    /**
     * @param string $code
     * @param string $name
     * @param string $parentName
     *
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws CityNotFoundException
     * @throws NotAuthorizedException
     * @throws BitrixRuntimeException
     * @throws SystemException
     * @return array|bool
     */
    public function setSelectedCity(string $code = '', string $name = '', string $parentName = '')
    {
        $city = null;
        if ($code) {
            $city = $this->locationService->findLocationCityByCode($code);
        } else {
            /** @noinspection PassingByReferenceCorrectnessInspection */
            $city = reset($this->locationService->findLocationCity($name, $parentName, 1, true));
        }

        if ($city) {
            /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
            setcookie('user_city_id', $city['CODE'], 86400 * 30);

            if ($this->isAuthorized()) {
                $this->userRepository->updateData($this->getCurrentUserId(), ['UF_LOCATION' => $city['CODE']]);
            }
        }

        return $city ?: false;
    }

    /**
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws NotAuthorizedException
     * @return array
     */
    public function getSelectedCity(): array
    {
        $cityCode = null;
        if ($_COOKIE['user_city_id']) {
            $cityCode = $_COOKIE['user_city_id'];
        } elseif ($this->isAuthorized()) {
            if (($user = $this->getCurrentUser()) && $user->getLocation()) {
                $cityCode = $user->getLocation();
            }
        }

        if ($cityCode) {
            try {
                return $this->locationService->findLocationCityByCode($cityCode);
            } catch (CityNotFoundException $e) {
            }
        }

        return $this->locationService->getDefaultLocation();
    }

    /**
     * @return UserRepository
     */
    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    /**
     * @param Client    $client
     * @param null|User $user
     *
     * @throws NotAuthorizedException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function setClientPersonalDataByCurUser(&$client, User $user = null)
    {
        if (!($user instanceof User)) {
            $user = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class)->getCurrentUser();
        }

        $client->birthDate = $user->getManzanaBirthday();
        $client->phone = $user->getManzanaNormalizePersonalPhone();
        $client->firstName = $user->getName();
        $client->secondName = $user->getSecondName();
        $client->lastName = $user->getLastName();
        $client->genderCode = $user->getManzanaGender();
        $client->email = $user->getEmail();
        $client->plLogin = $user->getLogin();
        $dateRegister = $user->getManzanaDateRegister();
        if ($dateRegister instanceof DateTime) {
            $client->plRegistrationDate = $user->getManzanaDateRegister();
        }
        if ($user->isEmailConfirmed() && $user->isPhoneConfirmed()) {
            // если e-mail и телефон подтверждены - отмечаем, что анкета актуальна и делаем карту бонусной
            // - так делалось по умолчанию на старом сайте
            $client->setActualContact(true);
            $client->setLoyaltyProgramContact(true);
        }
    }

    /**
     * @param int $id
     *
     * @throws InvalidIdentifierException
     * @throws NotAuthorizedException
     * @return array
     */
    public function getUserGroups(int $id = 0): array
    {
        if ($id === 0) {
            $id = $this->getCurrentUserId();
        }
        if ($id > 0) {
            return $this->userRepository->getUserGroupsIds($id);
        }

        return [];
    }

    /**
     * Авторизация текущего пользователя под другим пользователем
     *
     * @param int $id
     *
     * @throws NotAuthorizedException
     * @throws AvatarSelfAuthorizationException
     * @return bool
     */
    public function avatarAuthorize(int $id): bool
    {
        $authResult = false;

        /** @throws NotAuthorizedException */
        $curUserId = $this->getCurrentUserId();
        $hostUserId = $this->getAvatarHostUserId() ?: $curUserId;
        if ($hostUserId) {
            if ($hostUserId === $id) {
                throw new AvatarSelfAuthorizationException('An attempt to authenticate yourself');
            }
            $authResult = $this->bitrixUserService->Authorize($id);
            if ($authResult) {
                $this->setAvatarHostUserId($hostUserId);
                $this->setAvatarGuestUserId($id);
            }
        }

        return $authResult;
    }

    /**
     * @return int
     */
    public function getAvatarHostUserId(): int
    {
        $userId = 0;
        if (isset($_SESSION['4PAWS']['AVATAR_AUTH']['HOST_USER_ID'])) {
            $userId = (int)$_SESSION['4PAWS']['AVATAR_AUTH']['HOST_USER_ID'];
            $userId = $userId > 0 ? $userId : 0;
        }

        return $userId;
    }

    /**
     * @return int
     */
    public function getAvatarGuestUserId(): int
    {
        $userId = 0;
        if (isset($_SESSION['4PAWS']['AVATAR_AUTH']['GUEST_USER_ID'])) {
            $userId = (int)$_SESSION['4PAWS']['AVATAR_AUTH']['GUEST_USER_ID'];
            $userId = $userId > 0 ? $userId : 0;
        }

        return $userId;
    }

    /**
     * @return bool
     */
    public function isAvatarAuthorized(): bool
    {
        $isAuthorized = false;
        $hostUserId = $this->getAvatarHostUserId();
        $guestUserId = $this->getAvatarGuestUserId();
        if ($hostUserId > 0 && $guestUserId > 0) {
            $curUserId = 0;
            try {
                $curUserId = $this->getCurrentUserId();
            } catch (\Exception $exception) {
            }
            if ($curUserId === $guestUserId && $curUserId !== $hostUserId) {
                $isAuthorized = true;
            } else {
                $this->flushAvatarUserData();
            }
        }

        return $isAuthorized;
    }

    /**
     * Возврат к авторизации под исходным пользователем
     *
     * @throws NotAuthorizedException
     * @return bool
     */
    public function avatarLogout(): bool
    {
        $isLoggedByHostUser = true;
        /** @throws NotAuthorizedException */
        $curUserId = $this->getCurrentUserId();
        $hostUserId = $this->getAvatarHostUserId();
        if ($hostUserId) {
            $isLoggedByHostUser = false;
            if ($curUserId === $hostUserId) {
                $isLoggedByHostUser = true;
            } else {
                $authResult = $this->authorize($hostUserId);
                if ($authResult) {
                    $isLoggedByHostUser = true;
                }
            }
            if ($isLoggedByHostUser) {
                $this->flushAvatarUserData();
            }
        }

        return $isLoggedByHostUser;
    }

    /**
     * @return int
     *
     * получение либо скидки пользователя либо базовой
     */
    public function getDiscount(): int
    {
        if ($this->isAuthorized()) {
            try {
                return $this->getCurrentUser()->getDiscount();
            } catch (NotAuthorizedException $e) {
                /** показываем базовую скидку если не авторизованы */
            } catch (ConstraintDefinitionException|InvalidIdentifierException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка парамеров - ' . $e->getMessage());
            }
        }
        return static::BASE_DISCOUNT;
    }

    /**
     * @param User           $user
     *
     * @param null|UserBonus $userBonus
     *
     * @return int
     *
     * получение актуальной скидки пользователя(manzana)
     */
    public function getUserDiscount(User $user, ?UserBonus $userBonus = null): int
    {
        if (!($userBonus instanceof UserBonus)) {
            try {
                $userBonus = BonusService::getManzanaBonusInfo($user);
            } catch (ManzanaServiceContactSearchMoreOneException $e) {
                $logger = LoggerFactory::create('manzana');
                $logger->info(
                    'Найдено больше одного пользователя в манзане по телефону ' . $user->getPersonalPhone()
                );
            } catch (ManzanaServiceContactSearchNullException $e) {
                $logger = LoggerFactory::create('manzana');
                $logger->info('Не найдено пользователей в манзане по телефону ' . $user->getPersonalPhone());
            } catch (ManzanaServiceException $e) {
                $logger = LoggerFactory::create('manzana');
                $logger->error('Ошибка манзаны - ' . $e->getMessage());
            } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            } catch (NotAuthorizedException $e) {
                /** показываем базовую скидку если не авторизованы */
            } catch (ConstraintDefinitionException|InvalidIdentifierException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка парамеров - ' . $e->getMessage());
            }
        }
        if ($userBonus instanceof UserBonus && !$userBonus->isEmpty()) {
            return $userBonus->getRealDiscount();
        }
        return static::BASE_DISCOUNT;
    }

    /**
     * @return int
     *
     * получение актуальной скидки текущего пользователя(manzana)
     */
    public function getCurrentUserDiscount(): int
    {
        try {
            $curUser = $this->getCurrentUser();
            return $this->getUserDiscount($curUser);
        } catch (NotAuthorizedException $e) {
            /** показываем базовую скидку если не авторизованы */
        } catch (ConstraintDefinitionException|InvalidIdentifierException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка парамеров - ' . $e->getMessage());
        }
        return static::BASE_DISCOUNT;
    }

    /**
     * @param null|User $user
     * @param null|UserBonus $bonus
     *
     * @return bool
     *
     * обновление скидки пользователя по данным из manzana
     */
    public function refreshUserDiscount(?User $user = null, ?UserBonus $bonus=null): bool
    {
        if (!$user) {
            try {
                $user = $this->getCurrentUser();
            } catch (NotAuthorizedException $e) {
                return false;
            } catch (ConstraintDefinitionException|InvalidIdentifierException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка парамеров - ' . $e->getMessage());
                return false;
            }
        }

        if (!$user->getDiscountCardNumber()) {
            return false;
        }

        $newDiscount = (float)$this->getUserDiscount($user, $bonus);

        if ($user->getDiscount() !== $newDiscount) {
            try {
                return $this->getUserRepository()->updateData($user->getId(), ['UF_DISCOUNT' => $newDiscount]);
            } catch (SystemException $e) {
                $logger = LoggerFactory::create('system');
                $logger->error('Системная ошибка - ' . $e->getMessage());
                return false;
            } catch (ConstraintDefinitionException|InvalidIdentifierException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка парамеров - ' . $e->getMessage());
                return false;
            } catch (BitrixRuntimeException $e) {
                $logger = LoggerFactory::create('user');
                $logger->error('update error - ' . $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * @param int $id
     */
    protected function setAvatarHostUserId(int $id)
    {
        if ($id > 0) {
            $_SESSION['4PAWS']['AVATAR_AUTH']['HOST_USER_ID'] = $id;
        } else {
            if (isset($_SESSION['4PAWS']['AVATAR_AUTH']['HOST_USER_ID'])) {
                unset($_SESSION['4PAWS']['AVATAR_AUTH']['HOST_USER_ID']);
            }
        }
    }

    /**
     * @param int $id
     */
    protected function setAvatarGuestUserId(int $id)
    {
        if ($id > 0) {
            $_SESSION['4PAWS']['AVATAR_AUTH']['GUEST_USER_ID'] = $id;
        } else {
            if (isset($_SESSION['4PAWS']['AVATAR_AUTH']['GUEST_USER_ID'])) {
                unset($_SESSION['4PAWS']['AVATAR_AUTH']['GUEST_USER_ID']);
            }
        }
    }

    protected function flushAvatarUserData()
    {
        $this->setAvatarHostUserId(0);
        $this->setAvatarGuestUserId(0);
    }
}
