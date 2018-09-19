<?php

namespace FourPaws\SaleBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ForgotBasket
{
    /**
     * @var int
     *
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     *
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     */
    protected $id = 0;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_USER_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     *
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"create","read","update","delete"})
     */
    protected $userId = 0;

    /**
     * @var int
     *
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_TASK_TYPE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     *
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     * @Assert\Choice(callback={"FourPaws\SaleBundle\Enum\ForgotBasketEnum", "getTypes"}, groups={"create","read","update","delete"})
     */
    protected $type;

    /**
     * @var \DateTime
     *
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\SerializedName("UF_DATE_UPDATE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     *
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $dateUpdate;

    /**
     * @var \DateTime|null
     *
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\SerializedName("UF_DATE_EXEC")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Serializer\SkipWhenEmpty())
     *
     * @Assert\Blank(groups={"create"})
     */
    protected $dateExec;

    /**
     * @var boolean
     *
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("UF_ACTIVE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $active = false;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ForgotBasket
     */
    public function setId(int $id): ForgotBasket
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
     * @return ForgotBasket
     */
    public function setUserId(int $userId): ForgotBasket
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return ForgotBasket
     */
    public function setType(int $type): ForgotBasket
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdate(): \DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * @param \DateTime $dateUpdate
     * @return ForgotBasket
     */
    public function setDateUpdate(\DateTime $dateUpdate): ForgotBasket
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateExec(): ?\DateTime
    {
        return $this->dateExec;
    }

    /**
     * @param \DateTime|null $dateExec
     * @return ForgotBasket
     */
    public function setDateExec(?\DateTime $dateExec): ForgotBasket
    {
        $this->dateExec = $dateExec;

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
     * @return ForgotBasket
     */
    public function setActive(bool $active): ForgotBasket
    {
        $this->active = $active;

        return $this;
    }
}
