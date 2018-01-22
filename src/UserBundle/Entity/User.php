<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Entity;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
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
     */
    protected $id;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("EXTERNAL_AUTH_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $externalAuthId;
    
    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("ACTIVE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $active;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("XML_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $xmlId;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("LOGIN")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $login;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PASSWORD")
     * @Serializer\Groups(groups={"create","update"})
     * @Serializer\SkipWhenEmpty()
     * @Assert\NotBlank(groups={"create"})
     */
    protected $password;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PASSWORD")
     * @Serializer\Groups(groups={"read"})
     * @Assert\NotBlank(groups={"read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $encryptedPassword;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $name;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SECOND_NAME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $secondName;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("LAST_NAME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $lastName;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("EMAIL")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\Email(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $email;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PERSONAL_PHONE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @PhoneNumber(defaultRegion="RU",type="mobile")
     * @Serializer\SkipWhenEmpty()
     */
    protected $personalPhone;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CHECKWORD")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $checkWord;
    
    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_CONFIRMATION")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $personalDataConfirmed;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_LOCATION")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $location;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PERSONAL_GENDER")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $gender;
    
    /**
     * @var Date|null
     * @Serializer\Type("bitrix_date")
     * @Serializer\SerializedName("PERSONAL_BIRTHDAY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $birthday;
    
    /** @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_EMAIL_CONFIRMED")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $emailConfirmed;
    
    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_PHONE_CONFIRMED")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $phoneConfirmed;
    
    /**
     * @var DateTime|null
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_REGISTER")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateRegister;
    
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
     * @return bool
     */
    public function getActive() : bool
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
        return $this->xmlId ?? '';
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
    public function getNormalizePersonalPhone() : string
    {
        if (!empty($this->getPersonalPhone())) {
            try {
                return PhoneHelper::normalizePhone($this->getPersonalPhone());
            } catch (WrongPhoneNumberException $e) {
            }
        }
        
        return '';
    }
    
    /**
     * @return string
     */
    public function getPersonalPhone() : string
    {
        return $this->personalPhone ?? '';
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
    
    public function havePersonalPhone() : bool
    {
        return !empty($this->getPersonalPhone()) ? true : false;
    }
    
    /**
     * @return string
     */
    public function getCheckWord() : string
    {
        return $this->checkWord ?? '';
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
        $curPassword = $this->getEncryptedPassword();
        $salt        = substr($curPassword, 0, -32);
        $db_password = substr($curPassword, -32);
        
        return $db_password === md5($salt . $password);
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
        return $this->name ?? '';
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
        return $this->lastName ?? '';
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
        return $this->secondName ?? '';
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
     * @return null|string
     */
    public function getGender()
    {
        return $this->gender ?? null;
    }
    
    /**
     * @param string $gender
     *
     * @return User
     */
    public function setGender(string $gender) : User
    {
        $this->gender = $gender;
        
        return $this;
    }
    
    /**
     * @return int|null
     */
    public function getManzanaGender()
    {
        return str_replace(
                   [
                       'M',
                       'F',
                   ],
                   [
                       1,
                       2,
                   ],
                   $this->getGender()
               ) ?? null;
    }
    
    /**
     * @return \DateTimeImmutable|null
     */
    public function getManzanaBirthday()
    {
        $birthday = $this->getBirthday();
        if ($birthday instanceof Date) {
            return new \DateTimeImmutable($birthday->format('Y-m-d\TH:i:s'));
        }
        
        return null;
    }
    
    /**
     * @return null|Date
     *
     */
    public function getBirthday()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        if ($this->birthday instanceof Date) {
            return $this->birthday;
        }
        
        return null;
    }
    
    /**
     * @param null|Date $birthday
     *
     * @return User
     */
    public function setBirthday(Date $birthday) : User
    {
        $this->birthday = $birthday;
        
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isPhoneConfirmed() : bool
    {
        return $this->phoneConfirmed ?? false;
    }
    
    /**
     * @param bool $phoneConfirmed
     *
     * @return User
     */
    public function setPhoneConfirmed(bool $phoneConfirmed) : User
    {
        $this->phoneConfirmed = $phoneConfirmed;
        
        return $this;
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
     *
     * @return User
     */
    public function setExternalAuthId(string $externalAuthId) : User
    {
        $this->externalAuthId = $externalAuthId;
        
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isEmailConfirmed() : bool
    {
        return $this->emailConfirmed ?? false;
    }
    
    /**
     * @param bool $emailConfirmed
     *
     * @return User
     */
    public function setEmailConfirmed(bool $emailConfirmed) : User
    {
        $this->emailConfirmed = $emailConfirmed;
        
        return $this;
    }
    
    /**
     * @return \DateTimeImmutable
     */
    public function getManzanaDateRegister() : \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->getDateRegister()->format('Y-m-d\TH:i:s'));
    }
    
    /**
     * @return DateTime
     */
    public function getDateRegister() : DateTime
    {
        return $this->dateRegister;
    }
    
    /**
     * @param DateTime|null $dateRegister
     *
     * @return User
     */
    public function setDateRegister(DateTime $dateRegister) : User
    {
        $this->dateRegister = $dateRegister;
        
        return $this;
    }
}
