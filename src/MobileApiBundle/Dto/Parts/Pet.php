<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Parts;

use FourPaws\MobileApiBundle\Dto\Object\PetGender;
use FourPaws\MobileApiBundle\Dto\Object\PetPhoto;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

trait Pet
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @var int
     */
    protected $id;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("name")
     * @Assert\NotBlank()
     * @var string
     */
    protected $name;

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("category_id")
     * @Assert\NotBlank()
     * @var int
     */
    protected $categoryId;

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("breed_id")
     * @var int
     */
    protected $breedId;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("breed_other")
     * @var string
     */
    protected $breedOther;

    /**
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\PetGender")
     * @Serializer\SerializedName("gender")
     * @var PetGender
     */
    protected $gender;

    /**
     * @Serializer\Type("DateTime<'d.m.Y'>")
     * @Serializer\SerializedName("age_date")
     * @Assert\LessThan("tomorrow")
     * @var \DateTime
     */
    protected $birthday;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("age_string")
     * @var string
     */
    protected $birthdayString;

    /**
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\PetPhoto")
     * @Serializer\SerializedName("photo")
     * @var PetPhoto
     */
    protected $photo;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     * @return $this
     */
    public function setCategoryId(int $categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @return int
     */
    public function getBreedId(): int
    {
        return $this->breedId;
    }

    /**
     * @param int $breedId
     * @return $this
     */
    public function setBreedId(int $breedId)
    {
        $this->breedId = $breedId;
        return $this;
    }

    /**
     * @return string
     */
    public function getBreedOther(): string
    {
        return $this->breedOther;
    }

    /**
     * @param string $breedOther
     * @return $this
     */
    public function setBreedOther(string $breedOther)
    {
        $this->breedOther = $breedOther;
        return $this;
    }

    /**
     * @return PetGender
     */
    public function getGender(): PetGender
    {
        return $this->gender;
    }

    /**
     * @param PetGender $gender
     * @return $this
     */
    public function setGender(PetGender $gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday(): \DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     * @return $this
     */
    public function setBirthday(\DateTime $birthday)
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * @return string
     */
    public function getBirthdayString(): string
    {
        return $this->birthdayString;
    }

    /**
     * @param string $birthdayString
     * @return $this
     */
    public function setBirthdayString(string $birthdayString)
    {
        $this->birthdayString = trim($birthdayString);
        return $this;
    }

    /**
     * @return PetPhoto
     */
    public function getPhoto(): PetPhoto
    {
        return $this->photo;
    }

    /**
     * @param PetPhoto $photo
     * @return $this
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
        return $this;
    }
}
