<?php

namespace FourPaws\External\Manzana\Dto;

use DateTimeImmutable;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class BalanceRequest
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @Serializer\XmlRoot("BalanceRequest")
 * @Serializer\XmlNamespace(uri="http://loyalty.manzanagroup.ru/loyalty.xsd")
 */
class BalanceRequest
{
    public const ROOT_NAME = 'BalanceRequest';

    /**
     * Идентификатор запроса
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("RequestId")
     *
     * @var string
     */
    protected $requestId = '';

    /**
     * Номер карты
     *
     * @Serializer\Type("FourPaws\External\Manzana\Dto\Card")
     * @Serializer\SerializedName("Card")
     * @Serializer\Accessor(setter="setCard", getter="getCard")
     *
     * @var Card
     */
    protected $card;

    /**
     * Дата и время совершения операции
     * Дата не может быть больше текущей даты системы Manzana Loyalty
     *
     * @Serializer\Type("DateTimeImmutable<'Y-m-d\TH:i:s'>")
     * @Serializer\SerializedName("DateTime")
     *
     * @var DateTimeImmutable
     */
    protected $datetime;

    /**
     * Код Партнера
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Organization")
     *
     * @var string
     */
    protected $organization = '';

    /**
     * Код Магазина
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("BusinessUnit")
     *
     * @var string
     */
    protected $businessUnit = '';

    /**
     * Код POS терминала
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("POS")
     *
     * @var string
     */
    protected $pos = '';

    /**
     * ??
     *
     * @Serializer\Type("int")
     * @Serializer\SerializedName("ResponseType")
     *
     * @var
     */
    protected $responseType = 0; // какое-то костыльное поле, чтобы SOAP собрал request, иначе падает фатальная ошибка


    /**
     * @param string $requestId
     *
     * @return $this
     */
    public function setRequestId(string $requestId): self
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * @param DateTimeImmutable $datetime
     *
     * @return $this
     */
    public function setDatetime(DateTimeImmutable $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * @param string $organization
     *
     * @return $this
     */
    public function setOrganization(string $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @param string $businessUnit
     *
     * @return $this
     */
    public function setBusinessUnit(string $businessUnit): self
    {
        $this->businessUnit = $businessUnit;

        return $this;
    }

    /**
     * @param string $pos
     *
     * @return $this
     */
    public function setPos(string $pos): self
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * @param string $cardNumber
     *
     * @return $this
     */
    public function setCardByNumber(string $cardNumber): self
    {
        $this->setCard((new Card())->setNumber($cardNumber));

        return $this;
    }

    /**
     * @param Card $card
     *
     * @return $this
     */
    public function setCard(Card $card): self
    {
        if ($card->getNumber()) {
            $this->card = $card;
        }

        return $this;
    }

    /**
     * @return Card
     */
    public function getCard(): ?Card
    {
        return $this->card;
    }
}
