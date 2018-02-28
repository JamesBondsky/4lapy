<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class User
{
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
     * @return ClientCard
     */
    public function getCard(): ClientCard
    {
        return $this->card;
    }

    /**
     * @param ClientCard $card
     *
     * @return User
     */
    public function setCard(ClientCard $card): User
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
}
