<?php

namespace FourPaws\SaleBundle\Entity;

class ForgotBasket
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $fuserId;

    /**
     * @var \DateTime
     */
    public $dateCreate;

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
    public function getFuserId(): int
    {
        return $this->fuserId;
    }

    /**
     * @param int $fuserId
     * @return ForgotBasket
     */
    public function setFuserId(int $fuserId): ForgotBasket
    {
        $this->fuserId = $fuserId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreate(): \DateTime
    {
        return $this->dateCreate;
    }

    /**
     * @param \DateTime $dateCreate
     * @return ForgotBasket
     */
    public function setDateCreate(\DateTime $dateCreate): ForgotBasket
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }
}
