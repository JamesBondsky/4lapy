<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use FourPaws\PersonalBundle\Service\StampService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class User
{
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     */
    protected $id;

    /**
     * @var null|string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("email")
     * @Assert\Email()
     */
    protected $email;

    /**
     * @var null|string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("firstname")
     */
    protected $firstName;

    /**
     * @var null|string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("lastname")
     */
    protected $lastName;

    /**
     * @var null|string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("midname")
     */
    protected $midName;

    /**
     * @Serializer\SerializedName("birthdate")
     * @Serializer\Type("string")
     * @var null|\string
     */
    protected $birthDate;

    /**
     * @Serializer\SerializedName("phone")
     * @Serializer\Type("string")
     * @var null|string
     */
    protected $phone;

    /**
     * @Serializer\SerializedName("phone1")
     * @Serializer\Type("string")
     * @var null|string
     */
    protected $secondPhone;

    /**
     * @Serializer\Groups("response")
     * @Serializer\SerializedName("card")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\ClientCard")
     * @var null|ClientCard
     */
    protected $card;

    /**
     * @Serializer\Groups("response")
     * @Serializer\SerializedName("location")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\City")
     * @var null|City
     */
    protected $location;

    /**
     * @Serializer\Exclude(if="context.getDirection() === 1")
     * @see Read more about exclusion strategy here https://github.com/schmittjoh/JMSSerializerBundle/issues/619#issuecomment-347926659
     * @Serializer\SerializedName("locationId")
     * @Serializer\Type("string")
     * @var string
     */
    protected $locationId;

    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("stamps_income")
     * @var float
     */
    protected $stampsIncome = 0;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("stamps_offer_active")
     * @var bool
     */
//    protected $stampsOfferActive = StampService::IS_STAMPS_OFFER_ACTIVE;
    protected $stampsOfferActive = false;

    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("stamps_october_active")
     * @var bool
     */
    protected $octoberStampsOfferActive = false;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return User
     */
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return null|ClientCard
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param null|ClientCard $card
     *
     * @return User
     */
    public function setCard($card): User
    {
        $this->card = $card;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     *
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param null|string $firstName
     *
     * @return User
     */
    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param null|string $lastName
     *
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getMidName()
    {
        return $this->midName;
    }

    /**
     * @param null|string $midName
     *
     * @return User
     */
    public function setMidName(string $midName): User
    {
        $this->midName = $midName;
        return $this;
    }

    /**
     * @return null|\string
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param null|\string $birthDate
     *
     * @return User
     */
    public function setBirthDate(string $birthDate): User
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     *
     * @return User
     */
    public function setPhone(string $phone): User
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSecondPhone()
    {
        return $this->secondPhone;
    }

    /**
     * @param null|string $secondPhone
     *
     * @return User
     */
    public function setSecondPhone(string $secondPhone): User
    {
        $this->secondPhone = $secondPhone;
        return $this;
    }

    /**
     * @return City|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param City $location
     * @return User
     */
    public function setLocation(City $location): User
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @param string $locationId
     * @return User
     */
    public function setLocationId(string $locationId): User
    {
        $this->locationId = $locationId;
        return $this;
    }

    /**
     * @return float
     */
    public function getStampsIncome(): float
    {
        return $this->stampsIncome;
    }

    /**
     * @param float $stampsIncome
     * @return User
     */
    public function setStampsIncome(float $stampsIncome): User
    {
        $this->stampsIncome = $stampsIncome;
        return $this;
    }

    /**
     * @param bool $stampsOfferActive
     * @return User
     */
    public function setStampsOfferActive(bool $stampsOfferActive): User
    {
        $this->stampsOfferActive = $stampsOfferActive;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStampsOfferActive(): bool
    {
        return $this->stampsOfferActive;
    }
}
