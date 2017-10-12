<?php

namespace FourPaws\User;

use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\UserTable;
use FourPaws\BitrixOrm\Type\ResultContent;
use FourPaws\User\Exceptions\NotFoundException;
use FourPaws\User\Exceptions\TooManyUserFoundException;
use FourPaws\User\Exceptions\WrongPasswordException;
use FourPaws\User\Model\User;

class UserService
{
    const FIELD_LOGIN         = 'LOGIN';
    
    const FIELD_EMAIL         = 'EMAIL';
    
    const FIELD_PHONE         = 'PERSONAL_PHONE';
    
    const SOCSERV_EXTERNAL_ID = 'socservices';
    
    /**
     * UserService constructor.
     */
    public function __construct()
    {
    
    }
    
    /**
     * @return \FourPaws\User\Model\User
     */
    public function getCurrentUser() : User
    {
        global $USER;
        
        return $this->getUserById($USER->GetID());
    }
    
    /**
     * @param int $id
     *
     * @return \FourPaws\User\Model\User
     */
    public function getUserById(int $id) : User
    {
        return new User($id);
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
     * @throws \FourPaws\User\Exceptions\NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getLoginByRawLogin(string $rawLogin) : string
    {
        $result   = [];
        $userList = $this->getUserListByRawLogin($rawLogin);
        
        foreach ($userList as $user) {
            if ($user[self::FIELD_LOGIN] === $rawLogin) {
                $result[0] = $user[self::FIELD_LOGIN];
            }
            
            if ($user[self::FIELD_PHONE] === $rawLogin) {
                $result[1] = $user[self::FIELD_LOGIN];
            }
            
            if ($user[self::FIELD_EMAIL] === $rawLogin) {
                $result[2] = $user[self::FIELD_LOGIN];
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
     * @throws \Bitrix\Main\ArgumentException
     *
     * @return array
     */
    public function getUserListByRawLogin(string $rawLogin) : array
    {
        return UserTable::getList([
                                      'filter' => $this->getLoginFilter($rawLogin),
                                      'select' => [
                                          'ID',
                                          self::FIELD_LOGIN,
                                          self::FIELD_PHONE,
                                          self::FIELD_EMAIL,
                                      ],
                                  ])->fetchAll();
    }
    
    /**
     * @param string $rawLogin
     * @param string $password
     *
     * @return bool
     *
     * @throws \FourPaws\User\Exceptions\NotFoundException
     * @throws \FourPaws\User\Exceptions\WrongPasswordException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\User\Exceptions\TooManyUserFoundException
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
        
        return $cUser->IsAuthorized();
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
     * @return \Bitrix\Main\Entity\AddResult
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
     * @return \Bitrix\Main\Entity\UpdateResult
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
     * @throws \FourPaws\User\Exceptions\NotFoundException
     * @throws \FourPaws\User\Exceptions\TooManyUserFoundException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function checkLoginCount(string $login)
    {
        $count = UserTable::getList([
                                        'filter'  => $this->getLoginFilter($login),
                                        'select'  => ['CNT'],
                                        'runtime' => [
                                            new ExpressionField('CNT', 'COUNT(*)'),
                                        ],
                                    ])->fetchAll();
        
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
    
    public function restorePassword()
    {
    
    }
    
    /** @noinspection PhpTooManyParametersInspection
     *
     * @param string $rawLogin
     * @param string $checkword
     * @param string $password
     * @param string $confirmPassword
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\User\Exceptions\NotFoundException
     * @throws \FourPaws\User\Exceptions\TooManyUserFoundException
     *
     * @return \Bitrix\Main\Entity\UpdateResult
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
     * @param string $login
     *
     * @return array
     */
    public function getLoginFilter(string $login) : array
    {
        $filter = [
            'ACTIVE' => 'Y',
            [
                'LOGIC' => 'OR',
                [
                    '=' . self::FIELD_EMAIL => $login,
                ],
                [
                    '=' . self::FIELD_PHONE => $login,
                ],
                [
                    '=' . self::FIELD_LOGIN => $login,
                ],
            ],
        ];
        
        if ($email = $this->normalizeEmail($login)) {
            $filter[0][] = ['=' . self::FIELD_EMAIL => $email];
        }
        
        if ($phone = $this->normalizePhone($login)) {
            $filter[0][] = ['=' . self::FIELD_PHONE => $phone];
        }
        
        return $filter;
    }
    
    /**
     * @param string $phone
     *
     * @return string
     */
    public function normalizePhone(string $phone) : string
    {
        /**
         * @todo implement this
         */
        return $phone;
    }
    
    /**
     * @param string $email
     *
     * @return string
     */
    public function normalizeEmail(string $email) : string
    {
        /**
         * @todo implement this
         */
        return $email;
    }
    
    /**
     * @param int    $userId
     * @param string $rawPhone
     *
     * @return \Bitrix\Main\Entity\UpdateResult
     */
    public function verifyPhone(int $userId, string $rawPhone) : UpdateResult
    {
        $phone = $this->normalizePhone($rawPhone);
        
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
     * @return \Bitrix\Main\Entity\UpdateResult
     */
    public function verifyEmail(int $userId, string $rawEmail) : UpdateResult
    {
        $email = $this->normalizeEmail($rawEmail);
    
        /**
         * @todo implement $this
         */
        $result = new UpdateResult();
        $result->setPrimary($userId);
    
        return $result;
    }
}
