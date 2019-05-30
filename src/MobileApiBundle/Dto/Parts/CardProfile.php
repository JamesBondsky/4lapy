<?php

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use FourPaws\MobileApiBundle\Validation as MobileApiAssert;

trait CardProfile
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("last_name")
     * @var string
     */
    protected $lastName;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("first_name")
     * @var string
     */
    protected $firstName;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("patronymic")
     * @var string
     */
    protected $secondName;

    /**
     * @Serializer\Type("DateTime<'d.m.Y'>")
     * @Serializer\SerializedName("birth_date")
     * @var \DateTime
     */
    protected $birthDate;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("email")
     * @var string
     */
    protected $email;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("phone")
     * @var string
     */
    protected $phone;

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBirthDate(): \DateTime
    {
        return $this->birthDate;
    }

    /**
     * @param \DateTime $birthDate
     * @return $this
     */
    public function setBirthDate(\DateTime $birthDate)
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecondName(): string
    {
        return $this->secondName ?: '';
    }

    /**
     * @param string $secondName
     * @return $this
     */
    public function setSecondName(string $secondName)
    {
        $this->secondName = $secondName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email ?: '';
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
        return $this;
    }
}
