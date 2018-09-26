<?php

namespace FourPaws\SaleBundle\Dto\Notification;


use Bitrix\Sale\Basket;
use FourPaws\UserBundle\Entity\User;

class ForgotBasketNotification
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Basket
     */
    protected $basket;

    /**
     * @var float
     */
    protected $bonusCount;

    /**
     * @var string
     */
    protected $messageType;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return ForgotBasketNotification
     */
    public function setUser(User $user): ForgotBasketNotification
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Basket
     */
    public function getBasket(): Basket
    {
        return $this->basket;
    }

    /**
     * @param Basket $basket
     * @return ForgotBasketNotification
     */
    public function setBasket(Basket $basket): ForgotBasketNotification
    {
        $this->basket = $basket;

        return $this;
    }

    /**
     * @return float
     */
    public function getBonusCount(): float
    {
        return $this->bonusCount;
    }

    /**
     * @param float $bonusCount
     * @return ForgotBasketNotification
     */
    public function setBonusCount(float $bonusCount): ForgotBasketNotification
    {
        $this->bonusCount = $bonusCount;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessageType(): string
    {
        return $this->messageType;
    }

    /**
     * @param string $messageType
     * @return ForgotBasketNotification
     */
    public function setMessageType(string $messageType): ForgotBasketNotification
    {
        $this->messageType = $messageType;

        return $this;
    }
}
