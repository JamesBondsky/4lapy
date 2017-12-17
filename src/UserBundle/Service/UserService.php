<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\Location\LocationService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;

class UserService implements
    CurrentUserProviderInterface,
    UserAuthorizationInterface,
    UserRegistrationProviderInterface,
    UserCitySelectInterface
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

    public function __construct(UserRepository $userRepository, LocationService $locationService)
    {
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
     *
     * @return bool
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     */
    public function register(User $user): bool
    {
        return $this->userRepository->create($user);
    }

    /**
     * @param string $code
     * @param string $name
     *
     * @return bool
     * @throws CityNotFoundException
     */
    public function setSelectedCity(string $code = '', string $name = '', string $parentName = '') : bool
    {
        $city = null;
        if ($code) {
            $city = $this->locationService->findLocationCityByCode($code);
        } else {
            $city = reset($this->locationService->findLocationCity($name, $parentName, 1, true));
        }

        if (!$city) {
            return false;
        }

        setcookie('user_city_id', $city['CODE'], 86400 * 30);

        if ($this->isAuthorized()) {
            $user = $this->getCurrentUser();
            $user->setLocation($city['CODE']);
            $this->userRepository->update($user);
        }

        return true;
    }

    /**
     * @return array
     */
    public function getSelectedCity() : array
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
     * @return \FourPaws\UserBundle\Repository\UserRepository
     */
    public function getUserRepository() : UserRepository
    {
        return $this->userRepository;
    }
}
