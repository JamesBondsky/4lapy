<?php

namespace FourPaws\BitrixOrm\Model;

use FourPaws\BitrixOrm\Query\UserQuery;
use FourPaws\User\Exceptions\NotFoundException;

/**
 * Class User
 *
 * @param
 *
 * @package FourPaws\BitrixOrm\Model
 */
class User extends BitrixArrayItemBase
{
    protected $LOGIN;
    
    protected $NAME;
    
    protected $SECOND_NAME;
    
    protected $LAST_NAME;
    
    protected $EMAIL;
    
    protected $PERSONAL_PHONE;
    
    /**
     * @return string
     */
    public function getLogin() : string
    {
        return $this->LOGIN;
    }
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->NAME;
    }
    
    /**
     * @return string
     */
    public function getSecondName() : string
    {
        return $this->SECOND_NAME;
    }
    
    /**
     * @return string
     */
    public function getLastName() : string
    {
        return $this->LAST_NAME;
    }
    
    /**
     * @return string
     */
    public function getEmail() : string
    {
        return $this->EMAIL;
    }
    
    /**
     * @return string
     */
    public function getPersonalPhone() : string
    {
        return $this->PERSONAL_PHONE;
    }
    
    /**
     * @param string $login
     *
     * @return \FourPaws\BitrixOrm\Model\User
     */
    public function withLogin(string $login) : User
    {
        $this->LOGIN = $login;
        
        return $this;
    }
    
    /**
     * @param string $name
     *
     * @return \FourPaws\BitrixOrm\Model\User
     */
    public function withName(string $name) : User
    {
        $this->NAME = $name;
        
        return $this;
    }
    
    /**
     * @param string $secondName
     *
     * @return \FourPaws\BitrixOrm\Model\User
     */
    public function withSecondName(string $secondName) : User
    {
        $this->SECOND_NAME = $secondName;
        
        return $this;
    }
    
    /**
     * @param string $lastName
     *
     * @return \FourPaws\BitrixOrm\Model\User
     */
    public function withLastName(string $lastName) : User
    {
        $this->LAST_NAME = $lastName;
        
        return $this;
    }
    
    /**
     * @param string $email
     *
     * @return \FourPaws\BitrixOrm\Model\User
     */
    public function withEmail(string $email) : User
    {
        $this->EMAIL = $email;
        
        return $this;
    }
    
    /**
     * @param string $phone
     *
     * @return \FourPaws\BitrixOrm\Model\User
     */
    public function withPersonalPhone(string $phone) : User
    {
        $this->PERSONAL_PHONE = $phone;
        
        return $this;
    }
    
    /**
     * @param string $id
     *
     * @return \FourPaws\BitrixOrm\Model\ModelInterface
     *
     * @throws \FourPaws\User\Exceptions\NotFoundException
     */
    public static function createFromPrimary(string $id) : ModelInterface
    {
        $user = (new UserQuery())->withFilterParameter('ID', $id)->exec()->first();
        
        if (!$user) {
            throw new NotFoundException(sprintf('User with id %s is not found.', $id));
        }
        
        return $user;
    }
}