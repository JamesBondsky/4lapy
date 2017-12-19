<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Entity;

use Bitrix\Main\Type\Date;
use JMS\Serializer\Annotation as Serializer;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

class User
{
    const BITRIX_TRUE  = 'Y';
    
    const BITRIX_FALSE = 'N';
    
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $id;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("EXTERNAL_AUTH_ID")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $externalAuthId = 0;
    
    /**
     * @var bool
     * @Serializer\AccessType(type="public_method")
     * @Serializer\Accessor(getter="getRawActive", setter="setRawActive")
     * @Serializer\SerializedName("ACTIVE")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $active = true;
    
    /**
     * @Serializer\Type("string")
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("XML_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $xmlId = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("LOGIN")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $login = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PASSWORD")
     * @Serializer\Groups(groups={"create","update"})
     * @Serializer\SkipWhenEmpty()
     * @Assert\NotBlank(groups={"create"})
     */
    protected $password = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PASSWORD")
     * @Serializer\Groups(groups={"read"})
     * @Assert\NotBlank(groups={"read"})
     */
    protected $encryptedPassword = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $name = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SECOND_NAME")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $secondName = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("LAST_NAME")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $lastName = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("EMAIL")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\Email(groups={"create","read","update","delete"})
     */
    protected $email = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PERSONAL_PHONE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @PhoneNumber(defaultRegion="RU",type="mobile")
     * @Assert\Email(groups={"create","read","update","delete"})
     */
    protected $personalPhone = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CHECKWORD")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $checkWord = '';
    
    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_CONFIRMATION")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $personalDataConfirmed = false;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_LOCATION")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $location = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PERSONAL_GENDER")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $gender = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PERSONAL_BIRTHDAY")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $birthday = '';
    
    /** @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_EMAIL_CONFIRMED")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $emailConfirmed = false;
    
    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_PHONE_CONFIRMED")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $phoneConfirmed = false;
    
    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     *
     * @return User
     */
    public function setId(int $id) : User
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @param string $active
     *
     * @return User
     */
    public function setRawActive(string $active)
    {
        return $this->setActive($active === static::BITRIX_TRUE);
    }
    
    /**
     * @return string
     */
    public function getRawActive() : string
    {
        return $this->getActive() ? static::BITRIX_TRUE : static::BITRIX_FALSE;
    }
    
    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }
    
    /**
     * @param bool $active
     *
     * @return User
     */
    public function setActive(bool $active) : User
    {
        $this->active = $active;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getXmlId() : string
    {
        return $this->xmlId;
    }
    
    /**
     * @param string $xmlId
     *
     * @return User
     */
    public function setXmlId(string $xmlId) : User
    {
        $this->xmlId = $xmlId;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getEncryptedPassword() : string
    {
        return $this->encryptedPassword;
    }
    
    /**
     * @param string $encryptedPassword
     *
     * @return User
     */
    public function setEncryptedPassword(string $encryptedPassword) : User
    {
        $this->encryptedPassword = $encryptedPassword;
        
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
     * @return User
     */
    public function setEmail(string $email) : User
    {
        $this->email = $email;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPersonalPhone() : string
    {
        return $this->personalPhone;
    }
    
    /**
     * @param string $personalPhone
     *
     * @return User
     */
    public function setPersonalPhone(string $personalPhone) : User
    {
        $this->personalPhone = $personalPhone;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCheckWord() : string
    {
        return $this->checkWord;
    }
    
    /**
     * @param string $checkWord
     *
     * @return User
     */
    public function setCheckWord(string $checkWord) : User
    {
        $this->checkWord = $checkWord;
        
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isPersonalDataConfirmed() : bool
    {
        return $this->personalDataConfirmed;
    }
    
    /**
     * @param bool $personalDataConfirmed
     *
     * @return User
     */
    public function setPersonalDataConfirmed(bool $personalDataConfirmed) : User
    {
        $this->personalDataConfirmed = $personalDataConfirmed;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getLocation() : string
    {
        return (string)$this->location;
    }
    
    /**
     * @param string $location
     *
     * @return User
     */
    public function setLocation(string $location) : User
    {
        $this->location = $location;
        
        return $this;
    }
    
    /**
     * @param string $password
     *
     * @return bool
     */
    public function equalPassword(string $password) : bool
    {
        $curPassword = $this->getPassword();
        $salt        = substr($curPassword, 0, -32);
        $db_password = substr($curPassword, -32);
        
        return $db_password === md5($salt . $password);
    }
    
    /**
     * @return string
     */
    public function getPassword() : string
    {
        return $this->password;
    }
    
    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password) : User
    {
        $this->password = $password;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getFullName() : string
    {
        $name       = $this->getName();
        $lastName   = $this->getLastName();
        $secondName = $this->getSecondName();
        if (!empty($name)
            && !empty($secondName)
            && !empty($lastName)) {
            $fullName = $name . ' ' . $secondName . $lastName;
        } /** @noinspection NotOptimalIfConditionsInspection */ elseif (!empty($lastName)
                                                                        && !empty($name)) {
            $fullName = $lastName . ' ' . $name;
        } /** @noinspection NotOptimalIfConditionsInspection */ elseif (!empty($name)
                                                                        && !empty($secondName)) {
            $fullName = $name . ' ' . $secondName;
        } elseif (!empty($name)) {
            $fullName = $name;
        } else {
            $fullName = $this->getLogin();
        }
        
        return $fullName;
    }
    
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
     * @return User
     */
    public function setName(string $name) : User
    {
        $this->name = $name;
        
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
     * @return User
     */
    public function setLastName(string $lastName) : User
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
     * @return User
     */
    public function setSecondName(string $secondName) : User
    {
        $this->secondName = $secondName;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getLogin() : string
    {
        return $this->login;
    }
    
    /**
     * @param string $login
     *
     * @return User
     */
    public function setLogin(string $login) : User
    {
        $this->login = $login;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getGenderText() : string
    {
        $arGenders = [
            'M' => 'Мужской',
            'F' => 'Женский',
        ];
        
        return $arGenders[$this->getGender()] ?? '';
    }
    
    /**
     * @return string|null
     */
    public function getGender()
    {
        return $this->gender;
    }
    
    /**
     * @param string $gender
     */
    public function setGender(string $gender)
    {
        $this->gender = $gender;
    }
    
    /**
     * @return Date
     */
    public function getBirthday() : Date
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Date::createFromText($this->birthday);
    }
    
    /**
     * @param \Bitrix\Main\Type\Date $birthday
     */
    public function setBirthday(Date $birthday)
    {
        $this->birthday = $birthday->format('d.m.Y');
    }
    
    /**
     * @return bool
     */
    public function isPhoneConfirmed() : bool
    {
        return $this->phoneConfirmed;
    }
    
    /**
     * @param bool $phoneConfirmed
     */
    public function setPhoneConfirmed(bool $phoneConfirmed)
    {
        $this->phoneConfirmed = $phoneConfirmed;
    }
    
    /**
     * @return string
     */
    public function getExternalAuthId() : string
    {
        return $this->externalAuthId ?? '';
    }
    
    /**
     * @param string $externalAuthId
     */
    public function setExternalAuthId(string $externalAuthId)
    {
        $this->externalAuthId = $externalAuthId;
    }
    
    /**
     * @param string $password
     *
     * @return bool
     *
     */
    public function isEmailConfirmed() : bool
    {
        return $this->emailConfirmed;
    }
    
    /**
     * @param bool $emailConfirmed
     */
    public function setEmailConfirmed(bool $emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;
    }
}
