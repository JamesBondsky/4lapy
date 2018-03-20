<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Shares;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class BonusBuy
 * @package FourPaws\SapBundle\Dto\In\Shares
 *
 * @Serializer\XmlRoot(name="ns0:mt_BonusBuy")
 * @Serializer\XmlNamespace(uri="urn:4lapy.ru:ERP_2_BITRIX:DataExchange", prefix="ns0")
 */
class BonusBuy
{
    /**
     * Содержит код рекламного мероприятия, 10-значный цифровой код.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("RM_NR")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $shareId = '';

    /**
     * Дата начала рекламного мероприятия
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("START_DATE")
     * @Serializer\Type("DateTime<'Ymd'>")
     *
     * @var \DateTime
     */
    protected $startDate;

    /**
     * Дата окончания рекламного мероприятия
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("END_DATE")
     * @Serializer\Type("DateTime<'Ymd'>")
     *
     * @var \DateTime
     */
    protected $endDate;

    /**
     * Содержит признак выгрузки рекламного мероприятия на Сайт.
     * При значении «Х» рекламное мероприятие выгружается на Сайт.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("For_IM")
     * @Serializer\Type("sap_bool")
     *
     * @var bool
     */

    protected $forIm = false;
    /**
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("BONUS")
     * @Serializer\Type("sap_bool")
     *
     * @var bool
     */
    protected $bonus = false;

    /**
     * Если чекбокс установлен, акция рекламного мероприятия применяется один раз в чеке.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("SUM_PROMO")
     * @Serializer\Type("sap_bool")
     *
     * @var bool
     */
    protected $applyOnce = false;

    /**
     * Если чекбокс установлен, акции рекламного мероприятия не суммируются со скидкой за увеличение до упаковки.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("NOTSum_Pack")
     * @Serializer\Type("sap_bool")
     *
     * @var bool
     */
    protected $notApplyWithPackage = false;

    /**
     * Группа данных об акции Bonus Buy
     *
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Shares\BonusBuyShare>")
     * @Serializer\SerializedName("BB_HEAD")
     *
     * @var BonusBuyShare[]|Collection
     */
    protected $bonusBuyShare;

    /**
     * @param bool $applyOnce
     * @return BonusBuy
     */
    public function setApplyOnce(bool $applyOnce): BonusBuy
    {
        $this->applyOnce = $applyOnce;
        return $this;
    }

    /**
     * @param bool $notApplyWithPackage
     * @return BonusBuy
     */
    public function setNotApplyWithPackage(bool $notApplyWithPackage): BonusBuy
    {
        $this->notApplyWithPackage = $notApplyWithPackage;
        return $this;
    }

    /**
     * @param BonusBuyShare[]|Collection $bonusBuyShare
     * @return BonusBuy
     */
    public function setBonusBuyShare(Collection $bonusBuyShare): BonusBuy
    {
        $this->bonusBuyShare = $bonusBuyShare;
        return $this;
    }

    /**
     * @param string $shareId
     * @return BonusBuy
     */
    public function setShareId(string $shareId): BonusBuy
    {
        $this->shareId = $shareId;
        return $this;
    }

    /**
     * @param \DateTime $startDate
     * @return BonusBuy
     */
    public function setStartDate(\DateTime $startDate): BonusBuy
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @param \DateTime $endDate
     * @return BonusBuy
     */
    public function setEndDate(\DateTime $endDate): BonusBuy
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @param bool $forIm
     * @return BonusBuy
     */
    public function setForIm(bool $forIm): BonusBuy
    {
        $this->forIm = $forIm;
        return $this;
    }

    /**
     * @param bool $bonus
     * @return BonusBuy
     */
    public function setBonus(bool $bonus): BonusBuy
    {
        $this->bonus = $bonus;
        return $this;
    }

    /**
     * @return string
     */
    public function getShareId(): string
    {
        return $this->shareId;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * @return bool
     */
    public function isForIm(): bool
    {
        return $this->forIm;
    }

    /**
     * @return bool
     */
    public function isBonus(): bool
    {
        return $this->bonus;
    }

    /**
     * @return bool
     */
    public function isApplyOnce(): bool
    {
        return $this->applyOnce;
    }

    /**
     * @return bool
     */
    public function isNotApplyWithPackage(): bool
    {
        return $this->notApplyWithPackage;
    }

    /**
     * @return BonusBuyShare[]|Collection
     */
    public function getBonusBuyShare()
    {
        return $this->bonusBuyShare;
    }
}
