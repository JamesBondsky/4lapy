<?php

namespace FourPaws\User;

use Bitrix\Main\UserTable;
use FourPaws\User\Exceptions\NotFoundException;
use FourPaws\User\Exceptions\WrongPasswordException;
use FourPaws\User\Model\User;

class UserService
{
    const FIELD_LOGIN = 'LOGIN';
    
    const FIELD_EMAIL = 'EMAIL';
    
    const FIELD_PHONE = 'PERSONAL_PHONE';
    
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
     * @param string $login
     * @param string $password
     *
     * @return bool
     * @throws \FourPaws\User\Exceptions\NotFoundException
     * @throws \FourPaws\User\Exceptions\WrongPasswordException
     */
    public function login(string $login, string $password) : bool
    {
        /**
         * @todo нормалтзовывать телефон и email после логина
         */
        $userList = UserTable::getList([
                                           'filter' => [
                                               'ACTIVE' => 'Y',
                                               [
                                                   'LOGIC' => 'OR',
                                                   [
                                                       self::FIELD_EMAIL => $login,
                                                   ],
                                                   [
                                                       self::FIELD_PHONE => $login,
                                                   ],
                                                   [
                                                       self::FIELD_LOGIN => $login,
                                                   ],
                                               ],
                                           ],
                                           'select' => [
                                               self::FIELD_LOGIN,
                                           ],
                                       ])->fetchAll();
        
        if (!$userList) {
            throw new NotFoundException(sprintf('User login to "%s" string is not found', $login));
        }
        
        foreach ($userList as $user) {
            $result = (new \CUser())->Login($user[self::FIELD_LOGIN], $password);
            
            if ($result === true) {
                return true;
            }
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
    
    public function register(array $data)
    {
    
    }
}
