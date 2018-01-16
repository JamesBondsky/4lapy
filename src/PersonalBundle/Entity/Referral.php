<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Entity;

use Bitrix\Main\Type\Date;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Helpers\DateHelper;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Referral extends BaseEntity
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_USER_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $userId;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_LAST_NAME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $lastName;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_NAME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $name;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_SECOND_NAME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $secondName;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_CARD")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $card;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_PHONE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $phone;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_EMAIL")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $email;
    
    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("UF_MODERATED")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $moderate;
    
    /**
     * @var Date
     * @Serializer\Type("bitrix_date")
     * @Serializer\SerializedName("UF_CARD_CLOSED_DATE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateEndActive;
    
    /**
     * @var float
     */
    protected $bonus;
    
    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId ?? 0;
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
    public function getCard() : string
    {
        return $this->card ?? '';
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
        return $this->phone ?? '';
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
     * @return bool
     */
    public function isModerate() : bool
    {
        return $this->moderate ?? true;
    }
    
    /**
     * @param bool $moderate
     *
     * @return self
     */
    public function setModerate(bool $moderate) : self
    {
        $this->moderate = $moderate;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getFormatedActiveDate() : string
    {
        $dateEndActive = $this->getDateEndActive();
        if ($dateEndActive instanceof Date) {
            return DateHelper::replaceRuMonth($dateEndActive->format('d #m# Y'), DateHelper::NOMINATIVE);
        }
        
        return '';
    }
    
    /**
     * @return null|Date
     */
    public function getDateEndActive()
    {
        return $this->dateEndActive ?? null;
    }
    
    /**
     * @param null|Date|string $dateEndActive
     *
     * @return self
     */
    public function setDateEndActive($dateEndActive) : self
    {
        if (!($dateEndActive instanceof Date)) {
            if (\strlen($dateEndActive) > 0) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $this->dateEndActive = new Date($dateEndActive, 'd.m.Y');
            } else {
                $this->dateEndActive = null;
            }
        } else {
            $this->dateEndActive = $dateEndActive;
        }
        
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
            $fullName = $this->getEmail();
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
     * @return self
     */
    public function setName(string $name) : self
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
        return $this->secondName ?? '';
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
    public function getEmail() : string
    {
        return $this->email ?? '';
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
    
    /**
     * @return bool
     */
    public function isEndActiveDate() : bool
    {
        $dateEndActive = $this->getDateEndActive();
        
        return $dateEndActive instanceof Date && $dateEndActive->getTimestamp() <= time();
    }
    
    /**
     * @return float
     */
    public function getBonus() : float
    {
        return $this->bonus ?? (float)0;
    }
    
    /**
     * @param float $bonus
     *
     * @return self
     */
    public function setBonus(float $bonus) : self
    {
        $this->bonus = $bonus;
        
        return $this;
    }
}
