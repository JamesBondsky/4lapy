<?php

namespace FourPaws\External\ExpertSender\Dto;


use Bitrix\Sale\Basket;

class ForgotBasket
{
    /**
     * @var string
     */
    protected $userName = '';

    /**
     * @var string
     */
    protected $userEmail = '';

    /**
     * @var Basket
     */
    protected $basket;

    /**
     * @var int
     */
    protected $bonusCount = 0;

    /**
     * @var int
     */
    protected $messageType;

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return ForgotBasket
     */
    public function setUserName(string $userName): ForgotBasket
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    /**
     * @param string $userEmail
     * @return ForgotBasket
     */
    public function setUserEmail(string $userEmail): ForgotBasket
    {
        $this->userEmail = $userEmail;

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
     * @return ForgotBasket
     */
    public function setBasket(Basket $basket): ForgotBasket
    {
        $this->basket = $basket;

        return $this;
    }

    /**
     * @return int
     */
    public function getBonusCount(): int
    {
        return $this->bonusCount;
    }

    /**
     * @param int $bonusCount
     * @return ForgotBasket
     */
    public function setBonusCount(int $bonusCount): ForgotBasket
    {
        $this->bonusCount = $bonusCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getMessageType(): int
    {
        return $this->messageType;
    }

    /**
     * @param int $messageType
     * @return ForgotBasket
     */
    public function setMessageType(int $messageType): ForgotBasket
    {
        $this->messageType = $messageType;

        return $this;
    }
}
