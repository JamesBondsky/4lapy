<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use Bitrix\Sale\Fuser;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\Location\LocationService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\AvatarSelfAuthorizationException;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class UserService
 * @package FourPaws\UserBundle\Service
 */
class UserService implements
    CurrentUserProviderInterface,
    UserAuthorizationInterface,
    UserRegistrationProviderInterface,
    UserCitySelectInterface,
    UserAvatarAuthorizationInterface
{
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
     * @var ManzanaService
     */
    private $manzanaService;

    /**
     * UserService constructor.
     *
     * @param UserRepository  $userRepository
     * @param LocationService $locationService
     * @param ManzanaService  $manzanaService
     */
    public function __construct(
        UserRepository $userRepository,
        LocationService $locationService,
        ManzanaService $manzanaService
    ) {
        /**
         * todo move to factory service
         */
        global $USER;
        $this->bitrixUserService = $USER;
        $this->userRepository = $userRepository;
        $this->locationService = $locationService;
        $this->manzanaService = $manzanaService;
    }

    /**
     * @param string $rawLogin
     * @param string $password
     *
     * @return bool
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @throws InvalidCredentialException
     * @throws WrongPhoneNumberException
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

        return $this->isAuthorized();
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
     *
     *
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
     * @param User $user
     * @param bool $manzanaSave
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function register(User $user, bool $manzanaSave = true): bool
    {
        $result = $this->userRepository->create($user);

        if (!$manzanaSave) {
            return $result;
        }

        $client = null;
        try {
            $contactId = $this->manzanaService->getContactIdByPhone($user->getNormalizePersonalPhone());
            $client = new Client();
            $client->contactId = $contactId;
        } catch (ManzanaServiceException $e) {
            $client = new Client();
        }

        if ($client instanceof Client) {
            $this->manzanaService->updateContactAsync($client);
        }

        return true;
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
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
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
        // в Манзане телефон хранится с семеркой
        //$client->phone              = $user->getPersonalPhone();
        $client->phone = $user->getManzanaNormalizePersonalPhone();
        $client->firstName = $user->getName();
        $client->secondName = $user->getSecondName();
        $client->lastName = $user->getLastName();
        $client->genderCode = $user->getManzanaGender();
        $client->email = $user->getEmail();
        $client->plLogin = $user->getLogin();
        $client->plRegistrationDate = $user->getManzanaDateRegister();
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
