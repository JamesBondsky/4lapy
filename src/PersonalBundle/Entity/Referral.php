<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Entity;

use FourPaws\AppBundle\Entity\BaseEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Referral extends BaseEntity
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_USER_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $userId;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_LAST_NAME")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $lastName = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_NAME")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $name = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_SECOND_NAME")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $secondName = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_CARD")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     *
     */
    protected $card = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_PHONE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $phone = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_EMAIL")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $email = '';
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name) : self
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }
    
    /**
     * @param int $userId
     *
     * @return self
     */
    public function setUserId(int $userId) : self
    {
        $this->userId = $userId;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getLastName() : string
    {
        return $this->lastName;
    }
    
    /**
     * @param string $lastName
     *
     * @return self
     */
    public function setLastName(string $lastName) : self
    {
        $this->lastName = $lastName;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getSecondName() : string
    {
        return $this->secondName;
    }
    
    /**
     * @param string $secondName
     *
     * @return self
     */
    public function setSecondName(string $secondName) : self
    {
        $this->secondName = $secondName;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCard() : string
    {
        return $this->card;
    }
    
    /**
     * @param string $card
     *
     * @return self
     */
    public function setCard(string $card) : self
    {
        $this->card = $card;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPhone() : string
    {
        return $this->phone;
    }
    
    /**
     * @param string $phone
     *
     * @return self
     */
    public function setPhone(string $phone) : self
    {
        $this->phone = $phone;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }
    
    /**
     * @param string $email
     *
     * @return self
     */
    public function setEmail(string $email) : self
    {
        $this->email = $email;
        
        return $this;
    }
}
