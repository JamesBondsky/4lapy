<?php

namespace FourPaws\UserBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\GroupTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserAuthActionTable;
use Bitrix\Sale\Fuser;
use CAllUser;
use CUser;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Enum\CrudGroups;
use FourPaws\Enum\UserGroup;
use FourPaws\External\Exception\ManzanaCardIsNotFound;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Exception\TooManyActiveCardFound;
use FourPaws\External\ExpertsenderService;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Entity\ApiPushMessage;
use FourPaws\MobileApiBundle\Tables\ApiUserSessionTable;
use FourPaws\PersonalBundle\Entity\UserBonus;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\PersonalBundle\Service\PersonalOffersService as PersonalBundlePersonalOffersService;
use FourPaws\UserBundle\Entity\Group;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Enum\UserLocationEnum;
use FourPaws\UserBundle\Exception\AuthException;
use FourPaws\UserBundle\Exception\AvatarSelfAuthorizationException;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\EmptyPhoneException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\InvalidArgumentException;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Exception\RuntimeException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Repository\ManzanaOrdersImportUserRepository;
use FourPaws\UserBundle\Repository\UserRepository;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use WebArch\BitrixCache\BitrixCache;

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
    UserAvatarAuthorizationInterface,
    UserSearchInterface,
    LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const BASE_DISCOUNT = 3;
    /**
     * @var CAllUser|CUser
     */
    private $bitrixUserService;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ManzanaOrdersImportUserRepository
     */
    private $manzanaOrdersImportUserRepository;
    /**
     * @var LocationService
     */
    private $locationService;
    /**
     * @var ArrayCollection
     */
    private $userCollection;
    /**
     * @var ArrayTransformerInterface
     */
    private $transformer;

    /**
     * @var PersonalBundlePersonalOffersService $personalOffersService
     */
    private $personalOffersService;

    /**
     * UserService constructor.
     *
     * @param UserRepository $userRepository
     * @param LocationService $locationService
     * @param ManzanaOrdersImportUserRepository $manzanaOrdersImportUserRepository
     */
    public function __construct(
        UserRepository $userRepository,
        LocationService $locationService,
        ManzanaOrdersImportUserRepository $manzanaOrdersImportUserRepository,
        ArrayTransformerInterface $transformer
    )
    {
        /**
         * todo move to factory service
         */
        global $USER;

        if (\is_object($USER)) {
            $this->bitrixUserService = $USER;
        } else {
            $USER = new CUser();
            if (\is_object($USER)) {
                $this->bitrixUserService = $USER;
            } else {
                $this->bitrixUserService = null;
            }
        }

        $this->userRepository = $userRepository;
        $this->manzanaOrdersImportUserRepository = $manzanaOrdersImportUserRepository;
        $this->locationService = $locationService;
        $this->userCollection = new ArrayCollection();
        $this->transformer = $transformer;
    }

    /**
     * @param string $rawLogin
     * @param string $password
     *
     * @throws Exception
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @throws InvalidCredentialException
     * @throws WrongPhoneNumberException
     * @return bool
     */
    public function login(string $rawLogin, string $password): bool
    {
        $login = $this->userRepository->findLoginByRawLogin($rawLogin);
        if ($this->bitrixUserService !== null) {
            $result = $this->bitrixUserService->Login($login, $password);
            if ($result === true) {
                return true;
            }

            throw new InvalidCredentialException($result['MESSAGE']);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function logout(): bool
    {
        if ($this->bitrixUserService !== null) {
            $this->bitrixUserService->Logout();


            return !$this->isAuthorized();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {
        if ($this->bitrixUserService !== null) {
            return $this->bitrixUserService->IsAuthorized();
        }

        return false;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function authorize(int $id): bool
    {
        if ($this->bitrixUserService !== null) {
            $this->bitrixUserService->Authorize($id);

            return $this->isAuthorized();
        }

        return false;
    }

    /**
     * @throws NotAuthorizedException
     * @throws UsernameNotFoundException
     *
     * @return User
     */
    public function getCurrentUser(): User
    {
        $userId = $this->getCurrentUserId();
        try {
            if ($this->userCollection->containsKey($userId)) {
                $user = $this->userCollection->get($userId);
            } else {
                $user = $this->userRepository->find($userId);
                $this->userCollection->set($userId, $user);
            }
        } catch (Exception $e) {
            $user = null;
        }

        if ($user === null) {
            throw new UsernameNotFoundException('пользователь c id - ' . $userId . ' не найден');
        }

        return $user;
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
        if ($this->bitrixUserService !== null) {
            $id = (int)$this->bitrixUserService->GetID();
            if ($id > 0) {
                return $id;
            } else {
                throw new NotAuthorizedException('User is not authorized');
            }
        }
        throw new NotAuthorizedException('Trying to get user id without authorization');
    }

    /**
     *
     * @param User $user
     *
     * @throws Exception
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
        $validationResult = $this->userRepository->getValidator()
                                                 ->validate($user, null, ['create']);
        if ($validationResult->count() > 0) {
            throw new ValidationException($validationResult);
        }

        Application::getConnection()
                   ->startTransaction();

        $session = $_SESSION;
        try {
            $_SESSION['SEND_REGISTER_EMAIL'] = true;
            /** регистрируем битровым методом регистрации*/
            if ($this->bitrixUserService !== null) {
                $result = $this->bitrixUserService->Register(
                    $user->getLogin() ?? $user->getEmail(),
                    $user->getName() ?? '',
                    $user->getLastName() ?? '',
                    $user->getPassword(),
                    $user->getPassword(),
                    $user->getEmail()
                );
            } else {
                throw new Exception('не доступен сервис');
            }
            /** отправка письма происходи на событие after в этот момент */
        } catch (Exception $e) {
            Application::getConnection()
                       ->rollbackTransaction();
            $_SESSION = $session;
            throw new BitrixRuntimeException($e->getMessage(), $e->getCode());
        }

        $id = (int)($result['ID'] ?? '');

        if ($id <= 0) {
            Application::getConnection()
                       ->rollbackTransaction();
            $_SESSION = $session;
            if ($this->bitrixUserService !== null) {
                throw new BitrixRuntimeException($this->bitrixUserService->LAST_ERROR);
            }

            throw new BitrixRuntimeException('не доступен объект $USER');
        }

        $user
            ->setId($id)
            ->setActive(true);
        if (!$this->userRepository->update($user)) {
            Application::getConnection()
                       ->rollbackTransaction();
            $_SESSION = $session;
            throw new RuntimeException('Cant update registred user');
        }

        $registeredUser = $this->userRepository->find($id);
        if (!($registeredUser instanceof User)) {
            Application::getConnection()
                       ->rollbackTransaction();
            $_SESSION = $session;
            throw new RuntimeException('Cant fetch registred user');
        }
        Application::getConnection()
                   ->commitTransaction();

        return $registeredUser;
    }

    /**
     * @param string            $code
     * @param string            $name
     * @param string|array|null $parentName
     *
     * @throws Exception
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws CityNotFoundException
     * @throws NotAuthorizedException
     * @throws BitrixRuntimeException
     * @return array|bool
     */
    public function setSelectedCity(string $code = '', string $name = '', string $parentName = null)
    {
        $city = null;
        if ($code) {
            $city = $this->locationService->findLocationCityByCode($code);
        } else {
            /** @noinspection PassingByReferenceCorrectnessInspection */
            $city = reset($this->locationService->findLocationCityMultiple($name, $parentName, 1, true, false));
        }

        if ($city && $this->isAuthorized()) {
            $userId = $this->getCurrentUserId();
            if ($userId > 0) {
                $this->userRepository->updateData($userId, ['UF_LOCATION' => $city['CODE']]);
            } else {
                return false;
            }
        }

        if($city){
            foreach(GetModuleEvents("main", "OnCityChange", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, [$city]);
        }

        return $city ?: false;
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getSelectedCity(): array
    {
        $cityCode = null;

        if ($_COOKIE[UserLocationEnum::DEFAULT_LOCATION_COOKIE_CODE]) {
            $cityCode = $_COOKIE[UserLocationEnum::DEFAULT_LOCATION_COOKIE_CODE];
        } elseif ($this->isAuthorized()) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            if (($user = $this->getCurrentUser()) && $user->getLocation()) {
                $cityCode = $user->getLocation();
            }
        }

        $city = (new BitrixCache())
            ->withId(\sprintf(
                'location:%s',
                $cityCode ?? '-1'
            ))
            ->withTime(864000)
            ->resultOf(function () use ($cityCode) {
                $city = null;

                if ($cityCode) {
                    try {
                        $city = $this->locationService->findLocationCityByCode($cityCode);
                    } catch (CityNotFoundException $e) {
                    }
                }

                if (null === $city) {
                    $city = $this->locationService->getDefaultLocation();
                }

                return $city;
            });

        return $city;
    }

    /**
     * @return UserRepository
     */
    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    /**
     * @return ManzanaOrdersImportUserRepository
     */
    public function getManzanaOrdersImportUserRepository(): ManzanaOrdersImportUserRepository
    {
        return $this->manzanaOrdersImportUserRepository;
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
    public function setClientPersonalDataByCurUser(Client $client, User $user = null): void
    {
        if (!($user instanceof User)) {
            $user = App::getInstance()
                       ->getContainer()
                       ->get(CurrentUserProviderInterface::class)
                       ->getCurrentUser();
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
        $client->setActualContact();
        $client->setLoyaltyProgramContact();
    }

    public function setManzanaClientPersonalDataByUser(array $fields)
    {
        $client = new Client();

        if ($fields['PERSONAL_BIRTHDAY']) {
            $birthDate = new \DateTime($fields['PERSONAL_BIRTHDAY']);
        }

        if ($birthDate) {
            $result = new \DateTimeImmutable($birthDate->format('Y-m-d\TH:i:s'));
            $client->birthDate = $result;
        }
        if ($fields['PERSONAL_PHONE']) {
            $client->phone = PhoneHelper::getManzanaPhone($fields['PERSONAL_PHONE']);
        }
        $client->firstName = $fields['NAME'] ?? $client->firstName;
        $client->secondName = $fields['SECOND_NAME'] ?? $client->secondName;
        $client->lastName = $fields['LAST_NAME'] ?? $client->lastName;
        $client->genderCode = $fields['PERSONAL_GENDER'] ? str_replace(['M', 'F',], [1, 2], $fields['PERSONAL_GENDER']) : $client->genderCode;
        $client->email = $fields['EMAIL'] ?? $client->email;
        $client->plLogin = $fields['LOGIN'] ?? $client->plLogin;

        $client->setActualContact();
        $client->setLoyaltyProgramContact();

        return $client;
    }

    /**
     * @param int $id
     *
     * @throws Exception
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
     * @throws Exception
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
        if ($hostUserId > 0) {
            if ($hostUserId === $id) {
                throw new AvatarSelfAuthorizationException('An attempt to authenticate yourself');
            }
            if ($this->bitrixUserService !== null) {
                // logout - чтобы не смешивались корзины
                $this->bitrixUserService->Logout();
                $authResult = $this->bitrixUserService->Authorize($id);
            } else {
                $authResult = false;
            }
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
            try {
                $curUserId = $this->getCurrentUserId();
            } catch (Exception $exception) {
                $curUserId = 0;
            }
            if ($curUserId > 0 && $curUserId === $guestUserId && $curUserId !== $hostUserId) {
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
     * @throws Exception
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
            if ($curUserId > 0 && $curUserId === $hostUserId) {
                $isLoggedByHostUser = true;
            } else {
                if ($this->bitrixUserService !== null) {
                    // logout - чтобы не смешивались корзины
                    $this->bitrixUserService->Logout();
                }
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
     * @throws \RuntimeException
     */
    public function getDiscount(): int
    {
        if ($this->isAuthorized()) {
            try {
                return $this->getCurrentUser()
                            ->getDiscount();
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
     * @throws \FourPaws\External\Exception\
     * @throws \RuntimeException
     *
     * получение актуальной скидки пользователя(manzana)
     */
    public function getBonusPercent(User $user, ?UserBonus $userBonus = null): int
    {
        /**
         * @todo вынести логи, здесь этого не должно быть
         */

        if (null === $userBonus) {
            try {
                $userBonus = BonusService::getManzanaBonusInfo($user);
            } catch (ManzanaServiceContactSearchMoreOneException $e) {
                $this->log()
                     ->info(
                         \sprintf(
                             'Найдено больше одного пользователя в манзане по телефону %s',
                             $user->getPersonalPhone()
                         )
                     );
            } catch (ManzanaServiceContactSearchNullException $e) {
                $this->log()
                     ->info(
                         \sprintf(
                             'Не найдено пользователей в манзане по телефону %s',
                             $user->getPersonalPhone()
                         )
                     );
            } catch (EmptyPhoneException $e) {
                $this->log()
                     ->info('Нет телефона у пользователя - ' . $user->getId());
            } catch (ApplicationCreateException | ServiceNotFoundException | ServiceCircularReferenceException | ConstraintDefinitionException | InvalidIdentifierException | ManzanaServiceException $e) {
                $this->log()
                     ->error(
                         \sprintf(
                             'Ошибка получения процента бонуса %s',
                             $e->getMessage()
                         )
                     );
            } catch (NotAuthorizedException $e) {
                return static::BASE_DISCOUNT;
            }
        }

        return $userBonus && !$userBonus->isEmpty() ? $userBonus->getRealDiscount() : static::BASE_DISCOUNT;
    }

    /**
     * Получение актуального бонуса текущего пользователя
     *
     * @return int
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws \RuntimeException
     */
    public function getCurrentUserBonusPercent(): int
    {
        try {
            $curUser = $this->getCurrentUser();

            return $this->getBonusPercent($curUser);
        } catch (NotAuthorizedException $e) {
            /** показываем базовую скидку если не авторизованы */
        }

        return static::BASE_DISCOUNT;
    }

    /**
     * Обновление бонуса текущего пользователя
     *
     * @param null|User      $user
     * @param null|UserBonus $bonus
     *
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     */
    public function refreshUserBonusPercent(?User $user = null, ?UserBonus $bonus = null): void
    {
        if (!$user) {
            try {
                $user = $this->getCurrentUser();
            } catch (NotAuthorizedException $e) {
                /**
                 * Только для авторизованного
                 */
            }
        }

        $newDiscount = (float)$this->getBonusPercent($user, $bonus);

        if ($user->getDiscount() !== $newDiscount) {
            try {
                $this->getUserRepository()
                     ->updateData($user->getId(), ['UF_DISCOUNT' => $newDiscount]);
            } catch (BitrixRuntimeException $e) {
                $this->log()
                     ->error(
                         \sprintf(
                             'User #%d update error: %s',
                             $user->getId(),
                             $e->getMessage()
                         )
                     );
            }
        }
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function refreshUserOpt(User $user): bool
    {
        if (!$user->hasPhone()) {
            return false;
        }
        try {
            $manzanaService = App::getInstance()
                                 ->getContainer()
                                 ->get('manzana.service');
        } catch (ApplicationCreateException $e) {
            $this->log()
                 ->error('ошибка загрузки сервиса - manzana ', $e->getTrace());

            return false;
        }
        try {
            $contact = $manzanaService->getContactByUser($user);
        } catch (ApplicationCreateException $e) {
            /** не должно сюда доходить, так как передаем объект юзера */
            $this->log()
                 ->error('ошибка загрузки сервиса', $e->getTrace());

            return false;
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
            $this->log()
                 ->info('найдено больше одного пользователя с телефоном ' . $user->getPersonalPhone());

            return false;
        } catch (ManzanaServiceContactSearchNullException $e) {
            /** ошибка нам не нужна */
            return false;
        } catch (ManzanaServiceException $e) {
            $this->log()
                 ->error('ошибка манзаны', $e->getTrace());

            return false;
        }
        $groupsList = [];
        $groups = $user->getGroups()
                       ->toArray();
        /** @var Group $group */
        foreach ($groups as $group) {
            $groupsList[$group->getCode()] = $group->getId();
        }

        if ($contact->isOpt() && !$user->isOpt()) {
            /** установка оптовика */
            try {
                /** @noinspection OffsetOperationsInspection */
                $groupsList[] = GroupTable::query()
                                          ->setFilter(['STRING_ID' => UserGroup::OPT_CODE])
                                          ->setLimit(1)
                                          ->setSelect(['ID'])
                                          ->setCacheTtl(360000)
                                          ->exec()
                                          ->fetch()['ID'];
            } catch (ObjectPropertyException|ArgumentException|SystemException $e) {
                $this->log()
                     ->error('ошибка получения группы пользователя', $e->getTrace());

                return false;
            }
            CUser::SetUserGroup($user->getId(), $groupsList);
            $this->logout();
            $this->authorize($user->getId());
            TaggedCacheHelper::clearManagedCache(['personal:referral:' . $user->getId()]);

            return true;
        }
        if (!$contact->isOpt() && $user->isOpt()) {
            /** убираем оптовика */
            unset($groupsList[UserGroup::OPT_CODE]);
            CUser::SetUserGroup($user->getId(), $groupsList);
            $this->logout();
            $this->authorize($user->getId());
            TaggedCacheHelper::clearManagedCache(['personal:referral:' . $user->getId()]);

            return true;
        }

        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function refreshUserCard(User $user): bool
    {
        if (!$user->hasPhone()) {
            return false;
        }
        try {
            /**
             * @var ManzanaService $manzanaService
             */
            $manzanaService = App::getInstance()
                                 ->getContainer()
                                 ->get('manzana.service');
        } catch (ApplicationCreateException $e) {
            $this->log()
                 ->error('ошибка загрузки сервиса - manzana ', $e->getTrace());

            return false;
        }
        try {
            $contact = $manzanaService->getContactByUser($user);
        } catch (ApplicationCreateException $e) {
            /** не должно сюда доходить, так как передаем объект юзера */
            $this->log()
                 ->error('ошибка загрузки сервиса', $e->getTrace());

            return false;
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
            $this->log()
                 ->info('найдено больше одного пользователя с телефоном ' . $user->getPersonalPhone());

            return false;
        } catch (ManzanaServiceContactSearchNullException $e) {
            /** ошибка нам не нужна */
            return false;
        } catch (ManzanaServiceException $e) {
            $this->log()
                 ->error('ошибка манзаны', $e->getTrace());

            return false;
        }
        try {
            $manzanaService->updateUserCardByClient($contact);

            return true;
        } catch (ManzanaCardIsNotFound $e) {
            $this->log()
                 ->info('активных карт не найдено', $e->getTrace());
        } catch (TooManyUserFoundException $e) {
            $this->log()
                 ->info('найдено больше одного пользователя', $e->getTrace());
        } catch (UsernameNotFoundException $e) {
            $this->log()
                 ->info('пользователей в манзане не найдено по телефону', $e->getTrace());
        } catch (TooManyActiveCardFound $e) {
            $this->log()
                 ->info('найдено больше одной активной карты', $e->getTrace());
        } catch (ManzanaServiceException|Exception $e) {
            $this->log()
                 ->error('ошибка манзаны', $e->getTrace());
        }

        return false;
    }

    /**
     * Сбрасывает событие logout для юзера, чтобы его не разлогинивало
     *
     * @param User $user
     * @throws \Bitrix\Main\ObjectException
     */
    public function refreshUserAuthActions(User $user)
    {
        $bUser = new CUser;
        //calculate a session lifetime
        $policy = $bUser->GetSecurityPolicy();
        $phpSessTimeout = ini_get("session.gc_maxlifetime");
        if($policy["SESSION_TIMEOUT"] > 0)
        {
            $interval = min($policy["SESSION_TIMEOUT"]*60, $phpSessTimeout);
        }
        else
        {
            $interval = $phpSessTimeout;
        }
        $date = new DateTime();
        $date->add("-T".$interval."S");

        UserAuthActionTable::deleteByFilter(array(
            "=USER_ID" => $user->getId(),
            ">ACTION_DATE" => $date,
            "=ACTION" => 'logout',
        ));
    }

    /**
     * @param int $id
     *
     * @return User
     * @throws NotFoundException
     */
    public function findOne(int $id): User
    {
        $user = $this->userRepository->find($id);
        if (!$user instanceof User) {
            throw new NotFoundException(sprintf('User with id %s no found', $id));
        }

        return $user;
    }

    /**
     * @param string $phone
     * @param string $email
     *
     * @throws NotFoundException
     * @return User
     */
    public function findOneByPhoneOrEmail(string $phone, string $email): User
    {
        $user = null;
        try {
            $user = $this->findOneByPhone($phone);
        } catch (NotFoundException $e) {
        }

        if (!$user) {
            try {
                $user = $this->findOneByEmail($email);
            } catch (NotFoundException $e) {
            }
        }

        if (!$user) {
            throw new NotFoundException(sprintf(
                'No users found with phone %s and email %s',
                $phone,
                $email
            ));
        }

        return $user;
    }

    /**
     * @param string $email
     *
     * @throws NotFoundException
     * @return User
     */
    public function findOneByEmail(string $email): User
    {
        $users = $this->userRepository->findOneByEmail($email);
        if (empty($users)) {
            throw new NotFoundException(sprintf('No users found with email %s', $email));
        }

        return current($users);
    }

    /**
     * @param string $phone
     *
     * @throws NotFoundException
     * @return User
     */
    public function findOneByPhone(string $phone): User
    {
        $users = $this->userRepository->findOneByPhone($phone);
        if (empty($users)) {
            throw new NotFoundException(sprintf('No users found with phone %s', $phone));
        }

        return current($users);
    }

    /**
     * @todo для того чтобы заработал перед отправкой письма, необходимо сгенерирвоать и передать хеш в пиьсмо с типом auth_by_hash
     *
     * @param string             $hash
     * @param string             $email
     * @param string             $type
     * @param ConfirmCodeService $confirmService
     *
     * @return bool
     * @throws ApplicationCreateException
     * @throws WrongPhoneNumberException
     * @throws TooManyUserFoundException
     * @throws UsernameNotFoundException
     * @throws ExpiredConfirmCodeException
     * @throws NotFoundConfirmedCodeException
     * @throws AuthException
     */
    public function authByHash(string $hash, string $email, string $type = 'auth_by_hash', ConfirmCodeService $confirmService = null): bool
    {
        if (!empty($email) && !empty($hash)) {
            /** @var ConfirmCodeService $confirmService */
            if ($confirmService === null) {
                $confirmService = App::getInstance()
                                     ->getContainer()
                                     ->get(ConfirmCodeInterface::class);
            }
            if ($confirmService::checkCode($hash, $type)) {
                $userRepository = $this->getUserRepository();
                $userId = $userRepository->findIdentifierByRawLogin($email);
                if ($userId > 0) {
                    $user = null;
                    if ($this->isAuthorized()) {
                        $isAuthorized = true;
                        $curUser = $this->getCurrentUser();
                        if ($curUser->getId() === $userId) {
                            $user = $curUser;
                        }
                    } else {
                        $isAuthorized = false;
                        $user = $userRepository->find($userId);
                    }
                    if ($user !== null) {
                        if (!$isAuthorized) {
                            $this->authorize($userId);
                        }
                    } else {
                        throw new AuthException('Не найден пользователь');
                    }
                } else {
                    throw new AuthException('Не найден активный пользователь c эл. почтой ' . $email);
                }

                return true;
            }

            throw new AuthException('Проверка не пройдена');
        }

        return false;
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

    /**
     * @param string $login
     * @return bool
     */
    public function clearLoginAttempts(string $login) : bool
    {
        try {
            $userLogin = $this->userRepository->findLoginByRawLogin($login);
            $user = CUser::GetByLogin($userLogin)->Fetch();
            $obUser = new CUser;
            return $obUser->Update($user['ID'], ['LOGIN_ATTEMPTS' => 0]);
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * @todo log update errors
     * @param int $userId
     * @param string $newValue
     * @return bool
     */
    public function setModalsCounters(int $userId, string $newValue): bool
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException(__METHOD__ . '. userId: ' . $userId);
        }
        $user_class = new \CUser;
        $updateResult = $user_class->Update($userId, ['UF_MODALS_CNTS' => $newValue]);

        return $updateResult;
    }

    /**
     * @param array $userIds
     * @param int $idEvents
     * @param int|null $emailId
     * @param string $promocode
     * @param \DateTime $startDate
     * @param \DateTime|null $lastDate
     * @param bool|null $isOnlyEmail
     * @param string|null $field
     * @param int|null $promocodeId
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function sendNotifications(array $userIds, int $idEvents, ?int $emailId, string $promocode, \DateTime $startDate, ?\DateTime $lastDate, ?bool $isOnlyEmail = false, ?string $field = 'LOGIN', ?int $promocodeId = null)
    {
        $container = App::getInstance()->getContainer();
        $renderer = $container->get('templating');
        $this->personalOffersService = $container->get('personal_offers.service');

        if ($isOnlyEmail) {
            $field = 'ID';
        }

        $users = $this->userRepository->findBy([$field => $userIds]);

        $userIdsOrig = $userIds;

        $userIds = [];

        foreach ($users as $user) {
            $userIds[] = $user->getId();
        }

        $userIdsOrig = array_filter($userIdsOrig, function ($item) {
            return !empty(trim($item));
        });

        if (count($userIdsOrig) > 0) {
            $users = $this->userRepository->findBy(['PERSONAL_PHONE' => $userIdsOrig]);
            foreach ($users as $user) {
                $userIds[] = $user->getId();
            }
        }

        if (count($userIds) == 0) {
            return;
        }

        $filter = ['=USER_ID' => $userIds];
        $query = ApiUserSessionTable::query()->addSelect('USER_ID')->setFilter($filter);

        $dbResult = $query->exec();

        $resultIds = $dbResult->fetchAll();

        $userIdByEmail = [];
        $userIdByPush = [];

        foreach ($resultIds as $resultItem) {
            $userIdByPush[] = $resultItem['USER_ID'];
        }

        if (!is_array($userIdByPush)) {
            $userIdByPush = [$userIdByPush];
        }

        $userIdByPush = array_unique($userIdByPush);

        $userIdByEmail = array_diff($userIds, $userIdByPush);

        $textStart = $renderer->render('FourPawsSaleBundle:Push:coupon.new.start.html.php');
        $textLast = $renderer->render('FourPawsSaleBundle:Push:coupon.last.start.html.php');

        if (!$isOnlyEmail && count($userIdByPush) > 0) {
            $hlblock = \Bitrix\HighloadBlock\HighloadBlockTable::getList([
                'filter' => [
                    'TABLE_NAME' => 'api_push_messages'
                ]
            ])->fetch();

            $userField = (new \CUserTypeEntity())->GetList([], [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblock['ID'],
                'XML_ID' => 'UF_TYPE',
            ])->fetch();

            $type = (new \CUserFieldEnum())->GetList([], [
                'USER_FIELD_ID' => $userField['ID'],
                'XML_ID' => 'personal_offer',
            ])->fetch();

            $startDateSend = $startDate;

            $pushMessage = (new ApiPushMessage())
                ->setActive(true)
                ->setMessage($textStart)
                ->setUserIds($userIdByPush)
                ->setEventId($idEvents)
                ->setStartSend($startDateSend)
                ->setTypeId($type['ID']);

            $pushMessageLast = clone $pushMessage;

            if ($lastDate instanceof \DateTime) {
                $pushMessageLast->setStartSend($lastDate->modify('-4 day'));
            }
            $pushMessageLast->setMessage($textLast);

            $data = $this->transformer->toArray(
                $pushMessage,
                SerializationContext::create()->setGroups([CrudGroups::CREATE])
            );

            $dataLast = $this->transformer->toArray(
                $pushMessageLast,
                SerializationContext::create()->setGroups([CrudGroups::CREATE])
            );

            if (count($userIdByPush) > 0) {
                $hlBlockPushMessages = \FourPaws\App\Application::getHlBlockDataManager('bx.hlblock.pushmessages');
                $hlBlockPushMessages->add($data);
                if ($lastDate instanceof \DateTime) {
                    $hlBlockPushMessages->add($dataLast);
                }
            }
        }

        if ($emailId) {
            $userIdByEmail = $userIds;
            $users = $this->userRepository->findBy(['ID' => $userIdByEmail]);

            $barcodeGenerator = new BarcodeGeneratorPNG();
            if ($isOnlyEmail) {
                $offerFields = $this->personalOffersService->getOfferFieldsByCouponId(is_int($promocode) ? intval($promocode) : $promocodeId);
            } else {
                $offerFields = $this->personalOffersService->getOfferFieldsByPromoCode($promocode);
            }

            if ($offerFields->count() == 0) {
                throw new Exception('Купон по промокоду не найден');
            }

            $expertSender = $container->get('expertsender.service');

            $couponDescription = $offerFields->get('PREVIEW_TEXT');
            $couponDateActiveTo = $offerFields->get('DATE_ACTIVE_TO');
            $discountValue = $offerFields->get('PROPERTY_DISCOUNT_VALUE');

            foreach ($users as $user) {
                try {
                    $resSend = $expertSender->sendPersonalOfferCouponEmail(
                        $user->getId(),
                        $user->getName(),
                        $user->getEmail(),
                        $promocode,
                        'data:image/png;base64,' . base64_encode($barcodeGenerator->getBarcode($promocode, BarcodeGenerator::TYPE_CODE_128, 2.132310384278889, 127)),
                        $couponDescription,
                        $couponDateActiveTo,
                        $discountValue,
                        $emailId
                    );
                } catch (Exception $e) {
                }
            }
        }
    }
}
