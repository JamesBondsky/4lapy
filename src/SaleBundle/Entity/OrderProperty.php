<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Entity;

use FourPaws\App\Application;
use FourPaws\SaleBundle\Collection\OrderPropertyVariantCollection;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use JMS\Serializer\Annotation as Serializer;

class OrderProperty
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read", "update", "delete"})
     */
    protected $id = 0;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("PERSON_TYPE_ID")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $personTypeId = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $name = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("TYPE")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $type = '';

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("REQUIRED")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $required = false;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("USER_PROPS")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $userProps = false;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("DESCRIPTION")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $description = '';

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("IS_EMAIL")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $email = false;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("IS_PROFILE_NAME")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $profileName = false;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("IS_PAYER")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $payer = false;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("IS_LOCATION4TAX")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $locationForTax = false;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("IS_FILTERED")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $filtered = false;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CODE")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $code = '';

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("IS_ZIP")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $zip = false;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("IS_PHONE")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $phone = false;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("ACTIVE")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $active = true;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("UTIL")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $util = false;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("INPUT_FIELD_LOCATION")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $inputFieldLocation = 0;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("MULTIPLE")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $multiple = false;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("IS_ADDRESS")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $address = false;

    /**
     * @var array
     * @Serializer\Type("array<string>")
     * @Serializer\SerializedName("SETTINGS")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $settings = [];

    /**
     * @var OrderPropertyVariantCollection
     */
    protected $variants;

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
     * @return OrderProperty
     */
    public function setId(int $id): OrderProperty
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getPersonTypeId(): int
    {
        return $this->personTypeId;
    }

    /**
     * @param int $personTypeId
     *
     * @return OrderProperty
     */
    public function setPersonTypeId(int $personTypeId): OrderProperty
    {
        $this->personTypeId = $personTypeId;

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
     *
     * @return OrderProperty
     */
    public function setName(string $name): OrderProperty
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return OrderProperty
     */
    public function setType(string $type): OrderProperty
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return OrderProperty
     */
    public function setRequired(bool $required): OrderProperty
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUserProps(): bool
    {
        return $this->userProps;
    }

    /**
     * @param bool $userProps
     *
     * @return OrderProperty
     */
    public function setUserProps(bool $userProps): OrderProperty
    {
        $this->userProps = $userProps;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return OrderProperty
     */
    public function setDescription(string $description): OrderProperty
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmail(): bool
    {
        return $this->email;
    }

    /**
     * @param bool $email
     *
     * @return OrderProperty
     */
    public function setEmail(bool $email): OrderProperty
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return bool
     */
    public function isProfileName(): bool
    {
        return $this->profileName;
    }

    /**
     * @param bool $profileName
     *
     * @return OrderProperty
     */
    public function setProfileName(bool $profileName): OrderProperty
    {
        $this->profileName = $profileName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPayer(): bool
    {
        return $this->payer;
    }

    /**
     * @param bool $payer
     *
     * @return OrderProperty
     */
    public function setPayer(bool $payer): OrderProperty
    {
        $this->payer = $payer;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocationForTax(): bool
    {
        return $this->locationForTax;
    }

    /**
     * @param bool $locationForTax
     *
     * @return OrderProperty
     */
    public function setLocationForTax(bool $locationForTax): OrderProperty
    {
        $this->locationForTax = $locationForTax;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    /**
     * @param bool $filtered
     *
     * @return OrderProperty
     */
    public function setFiltered(bool $filtered): OrderProperty
    {
        $this->filtered = $filtered;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return OrderProperty
     */
    public function setCode(string $code): OrderProperty
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return bool
     */
    public function isZip(): bool
    {
        return $this->zip;
    }

    /**
     * @param bool $zip
     *
     * @return OrderProperty
     */
    public function setZip(bool $zip): OrderProperty
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPhone(): bool
    {
        return $this->phone;
    }

    /**
     * @param bool $phone
     *
     * @return OrderProperty
     */
    public function setPhone(bool $phone): OrderProperty
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return OrderProperty
     */
    public function setActive(bool $active): OrderProperty
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUtil(): bool
    {
        return $this->util;
    }

    /**
     * @param bool $util
     *
     * @return OrderProperty
     */
    public function setUtil(bool $util): OrderProperty
    {
        $this->util = $util;

        return $this;
    }

    /**
     * @return int
     */
    public function getInputFieldLocation(): int
    {
        return $this->inputFieldLocation;
    }

    /**
     * @param int $inputFieldLocation
     *
     * @return OrderProperty
     */
    public function setInputFieldLocation(int $inputFieldLocation): OrderProperty
    {
        $this->inputFieldLocation = $inputFieldLocation;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @param bool $multiple
     *
     * @return OrderProperty
     */
    public function setMultiple(bool $multiple): OrderProperty
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAddress(): bool
    {
        return $this->address;
    }

    /**
     * @param bool $address
     *
     * @return OrderProperty
     */
    public function setAddress(bool $address): OrderProperty
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     *
     * @return OrderProperty
     */
    public function setSettings(array $settings): OrderProperty
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return OrderPropertyVariantCollection
     */
    public function getVariants(): OrderPropertyVariantCollection
    {
        if (null === $this->variants) {
            /** @var OrderPropertyService $orderPropertyService */
            $orderPropertyService = Application::getInstance()->getContainer()->get(OrderPropertyService::class);
            $this->variants = $orderPropertyService->getPropertyVariants($this);
        }

        return $this->variants;
    }

    /**
     * @param OrderPropertyVariantCollection $variants
     *
     * @return OrderProperty
     */
    public function setVariants(OrderPropertyVariantCollection $variants): OrderProperty
    {
        $this->variants = $variants;

        return $this;
    }
}
