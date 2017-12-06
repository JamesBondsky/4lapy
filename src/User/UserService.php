<?php

namespace FourPaws\User;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\UserTable;
use FourPaws\BitrixOrm\Model\User;
use FourPaws\BitrixOrm\Type\ResultContent;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\Location\LocationService;
use FourPaws\User\Exceptions\NotFoundException;
use FourPaws\User\Exceptions\TooManyUserFoundException;
use FourPaws\User\Exceptions\WrongPasswordException;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;

class UserService
{
    const SOCSERV_EXTERNAL_ID = 'socservices';

    const FIAS_CODE_MOSCOW = '0c5b2444-70a0-4932-980c-b4dc0d3f02b5';

    /** @var  LocationService */
    protected $locationService;
    
    /**
     * UserService constructor.
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }
    
    /**
     * @return User
     *
     * @throws NotFoundException
     */
    public function getCurrentUser() : User
    {
        global $USER;
        
        return $USER->IsAuthorized() ? $this->getUserById($USER->GetID()) : new User();
    }
    
    /**
     * @param int $id
     *
     * @return User
     *
     * @throws NotFoundException
     */
    public function getUserById(int $id) : User
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return User::createFromPrimary($id);
    }
    
    /**
     * @param int $id
     *
     * @return bool
     */
    protected function authenticateById(int $id) : bool
    {
        global $USER;
        
        return $USER->Authorize($id);
    }
    
    /**
     * @param string $rawLogin
     *
     * @return string
     *
     * @throws NotFoundException
     * @throws ArgumentException
     */
    public function getLoginByRawLogin(string $rawLogin) : string
    {
        $result   = [];
        $userList = $this->getUserListByRawLogin($rawLogin);
        
        foreach ($userList as $user) {
            if ($user['LOGIN'] === $rawLogin) {
                $result[0] = $user['LOGIN'];
            }
            
            if ($user['PERSONAL_PHONE'] === $rawLogin) {
                $result[1] = $user['LOGIN'];
            }
            
            if ($user['EMAIL'] === $rawLogin) {
                $result[2] = $user['LOGIN'];
            }
        }
        
        if ($result) {
            return array_shift($result);
        }
        
        throw new NotFoundException(sprintf('Login %s is not found', $rawLogin));
    }
    
    /**
     * @param string $rawLogin
     *
     * @throws ArgumentException
     *
     * @return array
     */
    public function getUserListByRawLogin(string $rawLogin) : array
    {
        return UserTable::getList([
                                      'filter' => Utils::getLoginFilterByRaw($rawLogin),
                                      'select' => [
                                          'ID',
                                          'LOGIN',
                                          'EMAIL',
                                          'PERSONAL_PHONE',
                                      ],
                                  ])->fetchAll();
    }
    
    /**
     * @param string $rawLogin
     * @param string $password
     *
     * @return bool
     *
     * @throws NotFoundException
     * @throws WrongPasswordException
     * @throws ArgumentException
     * @throws TooManyUserFoundException
     */
    public function login(string $rawLogin, string $password) : bool
    {
        $this->checkLoginCount($rawLogin);
        $login = $this->getLoginByRawLogin($rawLogin);
        
        $result = (new \CUser())->Login($login, $password);
        
        if ($result === true) {
            return true;
        }
        
        throw new WrongPasswordException($result['MESSAGE']);
    }
    
    /**
     * @return bool
     */
    public function logout() : bool
    {
        $cUser = new \CUser();
        $cUser->Logout();
        
        return $this->isAuthorized();
    }
    
    /**
     * Current user is authorized
     *
     * @return bool
     */
    public function isAuthorized() : bool
    {
        global $USER;
        
        return $USER->IsAuthorized();
    }
    
    /**
     * @param array $data
     *
     * @return bool
     */
    public function register(array $data) : bool
    {
        
        
        return true;
    }
    
    /**
     * @param array $data
     *
     * @return AddResult
     */
    public static function add(array $data) : AddResult
    {
        $result = new AddResult();
        
        $cUser = new \CUser();
        $id    = $cUser->Add($data);
        
        if ($id) {
            $result->setId($id);
        } else {
            $result->addErrors([$cUser->LAST_ERROR]);
        }
        
        return $result;
    }
    
    /**
     * @param mixed $primary
     * @param array $data
     *
     * @return UpdateResult
     */
    public static function update($primary, array $data) : UpdateResult
    {
        $result = new UpdateResult();
        
        $cUser = new \CUser();
        $cUser->Update($primary, $data);
        
        if (!$cUser->Update($primary, $data)) {
            $result->addErrors([$cUser->LAST_ERROR]);
        }
        
        return $result;
    }
    
    /**
     * @param string $login
     *
     * @throws NotFoundException
     * @throws TooManyUserFoundException
     * @throws ArgumentException
     */
    public function checkLoginCount(string $login)
    {
        $count = UserTable::getList([
                                        'filter'  => Utils::getLoginFilterByRaw($login),
                                        'select'  => ['CNT'],
                                        'runtime' => [
                                            new ExpressionField('CNT', 'COUNT(*)'),
                                        ],
                                    ])->fetch();
        
        if ($count['CNT'] < 1) {
            throw new NotFoundException(sprintf('User with raw login %s is not found.',
                                                $login));
        }
        
        if ($count['CNT'] > 2) {
            throw new TooManyUserFoundException(sprintf('Too many user with login %s, user count with current id is %s.',
                                                        $login,
                                                        $count['CNT']));
        }
    }
    
    public function restorePassword(string $rawLogin)
    {
    
    }
    
    /**
     * @param string $rawLogin
     * @param string $checkword
     * @param string $password
     * @param string $confirmPassword
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws TooManyUserFoundException
     *
     * @return UpdateResult
     */
    public function changePassword(
        string $rawLogin,
        string $checkword,
        string $password,
        string $confirmPassword
    ) : UpdateResult
    {
        $result = new UpdateResult();
        
        $this->checkLoginCount($rawLogin);
        $login = $this->getLoginByRawLogin($rawLogin);
        
        $cUser         = new \CUser();
        $resultContent = new ResultContent($cUser->ChangePassword($login, $checkword, $password, $confirmPassword));
        
        if (!$resultContent->isSuccess()) {
            $result->addErrors([$resultContent->getMessage()]);
        }
        
        return $result;
    }
    
    /**
     * @param int    $userId
     * @param string $rawPhone
     *
     * @return UpdateResult
     */
    public function verifyPhone(int $userId, string $rawPhone) : UpdateResult
    {
        try {
            $phone = PhoneHelper::normalizePhone($rawPhone);
            /**
             * @todo implement this
             */
        } catch (WrongPhoneNumberException $e) {
            /**
             * @todo впилить проброс исключения
             */
        }
        
        /**
         * @todo implement $this
         */
        $result = new UpdateResult();
        $result->setPrimary($userId);
        
        return $result;
    }
    
    /**
     * @param int    $userId
     * @param string $rawEmail
     *
     * @return UpdateResult
     */
    public function verifyEmail(int $userId, string $rawEmail) : UpdateResult
    {
        $email = filter_var($rawEmail, FILTER_SANITIZE_EMAIL);
        
        /**
         * @todo implement this
         */
        $result = new UpdateResult();
        $result->setPrimary($userId);
        
        return $result;
    }

    /**
     * @param string $code
     * @param string $name
     *
     * @return bool
     * @throws \Exception
     */
    public function setSelectedCity(string $code = '', string $name = '', string $parentName = '') : bool
    {
        $city = null;
        if ($code) {
            $city = $this->locationService->findCityByCode($code);
        } else {
            $city = reset($this->locationService->findCity($name, $parentName, 1, true));
        }

        if (!$city) {
            return false;
        }

        setcookie('user_city_id', $city['CODE'], 86400 * 30);

        if ($this->isAuthorized()) {
            $user = $this->getCurrentUser();
            static::update($user->getId(), ['UF_LOCATION' => $city['CODE']]);
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
        } elseif (($user = $this->getCurrentUser()) && $user->getLocation()) {
            $cityCode = $user->getLocation();
        }

        if ($cityCode) {
            try {
                return $this->locationService->findCityByCode($cityCode);
            } catch (CityNotFoundException $e) {
            }
        }

        return $this->locationService->getDefaultCity();
    }
}
