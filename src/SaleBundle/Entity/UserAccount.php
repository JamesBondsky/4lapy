<?php

namespace FourPaws\SaleBundle\Entity;

use Bitrix\Currency\CurrencyManager;
use FourPaws\App\Application;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use FourPaws\SaleBundle\Validation as SaleValidation;
use JMS\Serializer\Annotation as Serializer;

class UserAccount
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read", "update", "delete"})
     * @Assert\NotBlank(groups={"read", "update", "delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read", "update", "delete"})
     */
    protected $id = 0;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("USER_ID")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     * @Assert\NotBlank(groups={"create", "read", "update", "delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"create", "read", "update","delete"})
     */
    protected $userId = 0;

    /**
     * @var \DateTime
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\SerializedName("TIMESTAMP_X")
     * @Serializer\Groups(groups={"read"})
     */
    protected $timestamp;

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CURRENT_BUDGET")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     * @Assert\GreaterThanOrEqual(value="0",groups={"create", "read", "update", "delete"})
     */
    protected $initialBudget = 0;

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CURRENT_BUDGET")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     * @Assert\GreaterThanOrEqual(value="0",groups={"create", "read", "update", "delete"})
     */
    protected $currentBudget = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CURRENCY")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     * @SaleValidation\Currency(groups={"create", "read", "update", "delete"})
     */
    protected $currency = '';

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("LOCKED")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $locked = false;

    /**
     * @var \DateTime
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\SerializedName("DATE_LOCKED")
     * @Serializer\Groups(groups={"read"})
     */
    protected $dateLocked;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NOTES")
     * @Serializer\Groups(groups={"create", "read", "update", "delete"})
     */
    protected $notes = '';

    /**
     * @var User
     */
    protected $user;

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
     * @return UserAccount
     */
    public function setId(int $id): UserAccount
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return UserAccount
     */
    public function setUserId(int $userId): UserAccount
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     *
     * @return UserAccount
     */
    public function setTimestamp(\DateTime $timestamp): UserAccount
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return float
     */
    public function getInitialBudget(): float
    {
        return $this->initialBudget;
    }

    /**
     * @return float
     */
    public function getCurrentBudget(): float
    {
        return $this->currentBudget;
    }

    /**
     * @param float $currentBudget
     *
     * @return UserAccount
     */
    public function setCurrentBudget(float $currentBudget): UserAccount
    {
        $this->currentBudget = $currentBudget;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        if (!$this->currency) {
            $this->currency = CurrencyManager::getBaseCurrency();
        }

        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return UserAccount
     */
    public function setCurrency(string $currency): UserAccount
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     *
     * @return UserAccount
     */
    public function setLocked(bool $locked): UserAccount
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getDateLocked()
    {
        return $this->dateLocked;
    }

    /**
     * @param \DateTime $dateLocked
     *
     * @return UserAccount
     */
    public function setDateLocked(\DateTime $dateLocked): UserAccount
    {
        $this->dateLocked = $dateLocked;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     *
     * @return UserAccount
     */
    public function setNotes(string $notes): UserAccount
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        if (!$this->user || ($this->userId !== $this->user->getId())) {
            /** @var CurrentUserProviderInterface $currentUserProvider */
            $currentUserProvider = Application::getInstance()
                                              ->getContainer()
                                              ->get(CurrentUserProviderInterface::class);
            $this->user = $currentUserProvider->getUserRepository()->find($this->userId);
        }

        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return UserAccount
     */
    public function setUser(User $user): UserAccount
    {
        $this->user = $user;
        $this->userId = $user->getId();

        return $this;
    }

}
