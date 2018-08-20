<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Entity;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use function foo\func;
use FourPaws\Enum\UserGroup;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User
 * @package FourPaws\UserBundle\Entity
 */
class User implements UserInterface
{
    public const BITRIX_TRUE = 'Y';

    public const BITRIX_FALSE = 'N';

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"dummy","read","delete"})
     * @Assert\NotBlank(groups={"read","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     */
    protected $id = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("EXTERNAL_AUTH_ID")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $externalAuthId = '';

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("ACTIVE")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $active = false;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("XML_ID")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $xmlId = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("LOGIN")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $login = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PASSWORD")
     * @Serializer\Groups(groups={"dummy","create"})
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
     * @Serializer\SkipWhenEmpty()
     */
    protected $encryptedPassword = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $name = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SECOND_NAME")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $secondName = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("LAST_NAME")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $lastName = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("EMAIL")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Assert\Email(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $email = '';

    /**
     * @var string
     * @Serializer\Type("phone")
     * @Serializer\SerializedName("PERSONAL_PHONE")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $personalPhone = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CHECKWORD")
     * @Serializer\Groups(groups={"dummy","create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $checkWord = '';

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_CONFIRMATION")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $personalDataConfirmed = false;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_LOCATION")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $location = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PERSONAL_GENDER")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $gender = '';

    /**
     * @var null|Date
     * @Serializer\Type("bitrix_date")
     * @Serializer\SerializedName("PERSONAL_BIRTHDAY")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $birthday;

    /** @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_EMAIL_CONFIRMED")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $emailConfirmed = false;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_PHONE_CONFIRMED")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $phoneConfirmed = false;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_SHOP")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $shopCode = '';

    /**
     * @var null|DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_REGISTER")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateRegister;

    /**
     * @var Collection|Role[]
     */
    protected $roles;

    /**
     * @var Collection|Group[]
     */
    protected $groups;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_INTERVIEW_MES")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $sendInterviewMsg = false;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_BONUS_MES")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $sendBonusMsg = false;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_FEEDBACK_MES")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $sendFeedbackMsg = false;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_PUSH_ORD_STAT")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $sendOrderStatusMsg = false;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_PUSH_NEWS")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $sendNewsMsg = false;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_PUSH_ACC_CHANGE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $sendBonusChangeMsg = false;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_SMS_MES")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $sendSmsMsg = false;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_EMAIL_MES")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $sendEmailMsg = false;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_GPS_MESS")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $gpsAllowed = false;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_DISCOUNT_CARD")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $discountCardNumber = '';

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_DISCOUNT")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $discount = 3;

    /** @var bool
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("UF_ES_SUBSCRIBED")
     * @Serializer\Groups(groups={"dummy","create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $esSubscribed = false;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

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
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return User
     */
    public function setActive(bool $active): User
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->xmlId ?? '';
    }

    /**
     * @param string $xmlId
     *
     * @return User
     */
    public function setXmlId(string $xmlId): User
    {
        $this->xmlId = $xmlId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email ?? '';
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasEmail(): bool
    {
        return !empty($this->getEmail());
    }

    /**
     * @return string
     */
    public function getNormalizePersonalPhone(): string
    {
        $result = '';
        if ($this->hasPhone()) {
            try {
                $result = PhoneHelper::normalizePhone($this->getPersonalPhone());
            } catch (WrongPhoneNumberException $e) {
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getManzanaNormalizePersonalPhone(): string
    {
        $value = $this->getNormalizePersonalPhone();

        return '' !== $value ? '7' . $value : '';
    }

    /**
     * @return string
     */
    public function getPersonalPhone(): string
    {
        return $this->personalPhone ?? '';
    }

    /**
     * @param string $personalPhone
     *
     * @return User
     */
    public function setPersonalPhone(string $personalPhone): User
    {
        try {
            $this->personalPhone = PhoneHelper::normalizePhone($personalPhone);
        } catch (WrongPhoneNumberException $e) {
            $this->personalPhone = '';
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPhone(): bool
    {
        return !empty($this->getPersonalPhone()) ? true : false;
    }

    /**
     * @return string
     */
    public function getCheckWord(): string
    {
        return $this->checkWord ?? '';
    }

    /**
     * @param string $checkWord
     *
     * @return User
     */
    public function setCheckWord(string $checkWord): User
    {
        $this->checkWord = $checkWord;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPersonalDataConfirmed(): bool
    {
        return $this->personalDataConfirmed;
    }

    /**
     * @param bool $personalDataConfirmed
     *
     * @return User
     */
    public function setPersonalDataConfirmed(bool $personalDataConfirmed): User
    {
        $this->personalDataConfirmed = $personalDataConfirmed;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return (string)$this->location;
    }

    /**
     * @param string $location
     *
     * @return User
     */
    public function setLocation(string $location): User
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function equalPassword(string $password): bool
    {
        $curPassword = $this->getEncryptedPassword();
        $salt = substr($curPassword, 0, -32);
        $db_password = substr($curPassword, -32);

        return $db_password === md5($salt . $password);
    }

    /**
     * @return string
     */
    public function getEncryptedPassword(): string
    {
        return $this->encryptedPassword;
    }

    /**
     * @param string $encryptedPassword
     *
     * @return User
     */
    public function setEncryptedPassword(string $encryptedPassword): User
    {
        $this->encryptedPassword = $encryptedPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        $name = $this->getName();
        $lastName = $this->getLastName();
        $secondName = $this->getSecondName();
        if (!empty($name)
            && !empty($secondName)
            && !empty($lastName)) {
            $fullName = $lastName . ' ' . $name . ' ' . $secondName;
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
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @param string $name
     *
     * @return User
     */
    public function setName(string $name): User
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName ?? '';
    }

    /**
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecondName(): string
    {
        return $this->secondName ?? '';
    }

    /**
     * @param string $secondName
     *
     * @return User
     */
    public function setSecondName(string $secondName): User
    {
        $this->secondName = $secondName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     *
     * @return User
     */
    public function setLogin(string $login): User
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getGenderText(): string
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
    public function setGender(string $gender): User
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return null|int
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
     * @return null|\DateTimeImmutable
     */
    public function getManzanaBirthday()
    {
        $result = null;
        $birthday = $this->getBirthday();
        if ($birthday !== null) {
            if ((int)$birthday->format('Y') >= 1900) {
                $result = new \DateTimeImmutable($birthday->format('Y-m-d\TH:i:s'));
            }
        }
        return $result;
    }

    /**
     * @return null|Date
     */
    public function getBirthday(): ?Date
    {
        $result = null;
        if ($this->birthday instanceof Date) { // а зачем этот if ?
            $result = $this->birthday;
        }
        return $result;
    }

    /**
     * @param null|Date $birthday
     *
     * @return User
     */
    public function setBirthday(Date $birthday = null): User
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPhoneConfirmed(): bool
    {
        return $this->phoneConfirmed ?? false;
    }

    /**
     * @param bool $phoneConfirmed
     *
     * @return User
     */
    public function setPhoneConfirmed(bool $phoneConfirmed): User
    {
        $this->phoneConfirmed = $phoneConfirmed;

        return $this;
    }

    /**
     * @return string
     */
    public function getExternalAuthId(): string
    {
        return $this->externalAuthId ?? '';
    }

    /**
     * @param string $externalAuthId
     *
     * @return User
     */
    public function setExternalAuthId(string $externalAuthId): User
    {
        $this->externalAuthId = $externalAuthId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmailConfirmed(): bool
    {
        return $this->emailConfirmed ?? false;
    }

    /**
     * @param bool $emailConfirmed
     *
     * @return User
     */
    public function setEmailConfirmed(bool $emailConfirmed): User
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getManzanaDateRegister(): \DateTimeImmutable
    {
        $dateRegister = $this->getDateRegister();
        if ($dateRegister instanceof DateTime) {
            if ((int)$dateRegister->format('Y') < 1900) {
                return null;
            }
            return new \DateTimeImmutable($dateRegister->format('Y-m-d\TH:i:s'));
        }
        return null; // @todo либо тайпхинт неверный либо null
    }

    /**
     * @return DateTime
     */
    public function getDateRegister(): DateTime
    {
        return $this->dateRegister;
    }

    /**
     * @param null|DateTime $dateRegister
     *
     * @return User
     */
    public function setDateRegister(DateTime $dateRegister): User
    {
        $this->dateRegister = $dateRegister;

        return $this;
    }

    /**
     * @return Collection|Group[]|Role[]
     */
    public function getRolesCollection()
    {
        $this->roles = $this->roles ?: new ArrayCollection();
        return $this->roles;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @param bool $withGroups
     *
     * @return array|Role[] The user roles
     */
    public function getRoles(bool $withGroups = true): array
    {
        $roles = $this->getRolesCollection()->toArray();
        if ($withGroups) {
            $groupRoles = $this
                ->getGroups()
                ->filter(function (Group $group) {
                    return $group->getCode();
                })
                ->map(function (Group $group) {
                    return new Role(strtoupper($group->getCode()));
                })
                ->toArray();
            $roles = array_merge($roles, $groupRoles);
        }
        return $roles;
    }

    /**
     * @param Role[] $roles
     *
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = (new ArrayCollection($roles))
            ->map(function ($role) {
                if (!$role) {
                    return null;
                }
                if (\is_string($role)) {
                    return new Role($role);
                }
                if ($role instanceof Role) {
                    return $role;
                }
                return null;
            })
            ->filter(function ($role) {
                return $role;
            });
        return $this;
    }

    /**
     * @param Role $role
     *
     * @return bool
     */
    public function addRole(Role $role)
    {
        return $this->getRolesCollection()->add($role);
    }

    /**
     * @param Role $role
     *
     * @return bool
     */
    public function removeRole(Role $role)
    {
        return $this->getRolesCollection()->removeElement($role);
    }

    /**
     * @return ArrayCollection|Collection|Group[]
     */
    // @todo конкретизировать возвращаемый тип
    public function getGroups(): Collection
    {
        $this->groups = $this->groups ?: new ArrayCollection();
        return $this->groups;
    }

    /**
     * @return array
     */
    public function getGroupsIds(): array
    {
        $result = [];
        if ($groups = $this->getGroups()) {
            $result = $groups->map(
                function (Group $e) {
                    return $e->getId();
                }
            )->toArray();
        }
        return $result;
    }

    /**
     * @param Collection|Group[] $groups
     *
     * @return User
     */
    public function setGroups(Collection $groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return null|string The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getLogin();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        $this->password = '';
        return $this;
    }

    /**
     * @return string
     */
    public function getDiscountCardNumber(): string
    {
        return $this->discountCardNumber ?? '';
    }

    /**
     * @param string $discountCardNumber
     *
     * @return User
     */
    public function setDiscountCardNumber(string $discountCardNumber): User
    {
        $this->discountCardNumber = $discountCardNumber;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFastOrderUser(): bool
    {
        return (strpos($this->getEmail(), '@fastorder.ru') !== false);
    }

    /**
     * @return string
     */
    public function getShopCode(): string
    {
        return $this->shopCode ?? '';
    }

    /**
     * @param string $shopCode
     *
     * @return User
     */
    public function setShopCode(string $shopCode): User
    {
        $this->shopCode = $shopCode;

        return $this;
    }


    /**
     * @return bool
     */
    public function isSendInterviewMsg(): bool
    {
        return $this->sendInterviewMsg ?? false;
    }

    /**
     * @param bool $sendInterviewMsg
     *
     * @return User
     */
    public function setSendInterviewMsg(bool $sendInterviewMsg): User
    {
        $this->sendInterviewMsg = $sendInterviewMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendBonusMsg(): bool
    {
        return $this->sendBonusMsg ?? false;
    }

    /**
     * @param bool $sendBonusMsg
     *
     * @return User
     */
    public function setSendBonusMsg(bool $sendBonusMsg): User
    {
        $this->sendBonusMsg = $sendBonusMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendFeedbackMsg(): bool
    {
        return $this->sendFeedbackMsg ?? false;
    }

    /**
     * @param bool $sendFeedbackMsg
     *
     * @return User
     */
    public function setSendFeedbackMsg(bool $sendFeedbackMsg): User
    {
        $this->sendFeedbackMsg = $sendFeedbackMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendOrderStatusMsg(): bool
    {
        return $this->sendOrderStatusMsg ?? false;
    }

    /**
     * @param bool $sendOrderStatusMsg
     *
     * @return User
     */
    public function setSendOrderStatusMsg(bool $sendOrderStatusMsg): User
    {
        $this->sendOrderStatusMsg = $sendOrderStatusMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendNewsMsg(): bool
    {
        return $this->sendNewsMsg ?? false;
    }

    /**
     * @param bool $sendNewsMsg
     *
     * @return User
     */
    public function setSendNewsMsg(bool $sendNewsMsg): User
    {
        $this->sendNewsMsg = $sendNewsMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendBonusChangeMsg(): bool
    {
        return $this->sendBonusChangeMsg ?? false;
    }

    /**
     * @param bool $sendBonusChangeMsg
     *
     * @return User
     */
    public function setSendBonusChangeMsg(bool $sendBonusChangeMsg): User
    {
        $this->sendBonusChangeMsg = $sendBonusChangeMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendSmsMsg(): bool
    {
        return $this->sendSmsMsg ?? false;
    }

    /**
     * @param bool $sendSmsMsg
     *
     * @return User
     */
    public function setSendSmsMsg(bool $sendSmsMsg): User
    {
        $this->sendSmsMsg = $sendSmsMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendEmailMsg(): bool
    {
        return $this->sendEmailMsg ?? false;
    }

    /**
     * @param bool $sendEmailMsg
     *
     * @return User
     */
    public function setSendEmailMsg(bool $sendEmailMsg): User
    {
        $this->sendEmailMsg = $sendEmailMsg;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGpsAllowed(): bool
    {
        return $this->gpsAllowed ?? false;
    }

    /**
     * @param bool $gpsAllowed
     *
     * @return User
     */
    public function setGpsAllowed(bool $gpsAllowed): User
    {
        $this->gpsAllowed = $gpsAllowed;
        return $this;
    }

    /**
     * @return bool
     */
    public function allowedEASend(): bool
    {
        return $this->hasEmail() && $this->isEmailConfirmed();
    }

    /**
     * @return int
     */
    public function getDiscount(): int
    {
        return $this->discount ?? 3;
    }

    /**
     * @param int $discount
     */
    public function setDiscount(int $discount): void
    {
        $this->discount = $discount;
    }

    /**
     * @return bool
     */
    public function isOpt(): bool
    {
        $groups = $this->getGroups()->toArray();
        /** @var Group $group */
        foreach ($groups as $group) {
            if ($group->getCode() === UserGroup::OPT_CODE) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isEsSubscribed(): bool
    {
        return $this->esSubscribed ?? false;
    }

    /**
     * @param bool $esSubscribed
     */
    public function setEsSubscribed(bool $esSubscribed): void
    {
        $this->esSubscribed = $esSubscribed;
    }
}
