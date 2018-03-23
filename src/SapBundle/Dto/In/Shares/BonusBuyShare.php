<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Shares;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class BonusBuyShare
 *
 * @package FourPaws\SapBundle\Dto\In\Shares
 */
class BonusBuyShare
{
    const ACT_MODIFY = 'MODI';
    const ACT_DELETE = 'DELE';

    /**
     * Код региона
     *
     * Если значение пусто или IM01, то цена по акции действует на всем Сайте без ограничения по региону или способу
     * доставки.
     * Если значение IRхх, где хх – это числовой код региона из справочника, то на этот товар для этого региона
     * действует указанная акция вне зависимости от наличия других акций на этот товар.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("PLANT")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $region = '';

    /**
     * Содержит код акции, 12-значный цифровой код.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("BBY_NR")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $shareNumber = '';

    /**
     * Содержит название акции.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("SHORT_TEXT")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $description = '';

    /**
     * Содержит дату активации акции
     * Не является датой начала акции.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("AV_DATE")
     * @Serializer\Type("DateTime<'Ymd'>")
     *
     * @var \DateTime
     */
    protected $activationDate;

    /**
     * Содержит дату изменения акции
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("CH_DATE")
     * @Serializer\Type("DateTime<'Ymd'>")
     *
     * @var \DateTime
     */
    protected $endDate;

    /**
     * Содержит тип условия акции. Тип поля – единственный выбор из значений:
     *
     * - ВВ01 – цена со скидкой;
     * - ВВ02 – абсолютная скидка;
     * - ВВ03 – скидка в процентах.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("COND_TYPE")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $conditionType = '';

    /**
     * Содержит индикатор изменения действия акции. Тип поля – единственный выбор из значений:
     *
     * - MODI – модификация, значение по умолчанию;
     * - DELE – удаление. При выбранном значении акция должна быть удалена.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("AENDKENNZ")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $act = '';

    /**
     * Содержит сумму чека, при достижении которой акция начинает действовать.
     * Поле необязательно для заполнения. Если поле заполнено, параметр MAT_QUAN в группе данных
     * Purchase_Item не учитывается
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("RM_NR")
     * @Serializer\Type("float")
     *
     * @var string
     */
    protected $amount = '';

    /**
     * Содержит тип механики акции. Варианты значений:
     *
     * - Z005 – Акция «N+M»;
     * - Z006 – Акция «M по цене N»;
     * - Z008 – Скидка на товар за покупку от суммы;
     * - Z011 – Скидка на товары по акции
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("BB_TYPE")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $type = '';

    /**
     * Содержит текст шильдика акции.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("SHILDIK")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $mark = '';

    /**
     * Группа данных о предпосылке акции
     *
     * @Serializer\XmlList(inline=true, entry="PURCHASE_HEAD")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Shares\BonusBuyFrom>")
     *
     * @var BonusBuyFrom[]|Collection
     */
    protected $bonusBuyFrom;

    /**
     * Группа данных о подарках (элементы, на которые действуют акции)
     *
     * @Serializer\XmlList(inline=true, entry="BONUS_HEAD")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Shares\BonusBuyTo>")
     *
     * @var BonusBuyTo[]|Collection
     */
    protected $bonusBuyTo;

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @param string $region
     * @return BonusBuyShare
     */
    public function setRegion(string $region): BonusBuyShare
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return string
     */
    public function getShareNumber(): string
    {
        return $this->shareNumber;
    }

    /**
     * @param string $shareNumber
     * @return BonusBuyShare
     */
    public function setShareNumber(string $shareNumber): BonusBuyShare
    {
        $this->shareNumber = $shareNumber;

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
     * @return BonusBuyShare
     */
    public function setDescription(string $description): BonusBuyShare
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getActivationDate(): \DateTime
    {
        return $this->activationDate;
    }

    /**
     * @param \DateTime $activationDate
     * @return BonusBuyShare
     */
    public function setActivationDate(\DateTime $activationDate): BonusBuyShare
    {
        $this->activationDate = $activationDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return BonusBuyShare
     */
    public function setEndDate(\DateTime $endDate): BonusBuyShare
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getConditionType(): string
    {
        return $this->conditionType;
    }

    /**
     * @param string $conditionType
     * @return BonusBuyShare
     */
    public function setConditionType(string $conditionType): BonusBuyShare
    {
        $this->conditionType = $conditionType;

        return $this;
    }

    /**
     * @return string
     */
    public function getAct(): string
    {
        return $this->act;
    }

    /**
     * @param string $act
     * @return BonusBuyShare
     */
    public function setAct(string $act): BonusBuyShare
    {
        $this->act = $act;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return BonusBuyShare
     */
    public function setAmount(string $amount): BonusBuyShare
    {
        $this->amount = $amount;

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
     * @return BonusBuyShare
     */
    public function setType(string $type): BonusBuyShare
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getMark(): string
    {
        return $this->mark;
    }

    /**
     * @param string $mark
     * @return BonusBuyShare
     */
    public function setMark(string $mark): BonusBuyShare
    {
        $this->mark = $mark;

        return $this;
    }

    /**
     * @return BonusBuyTo[]|Collection
     */
    public function getBonusBuyTo()
    {
        return $this->bonusBuyTo;
    }

    /**
     * @param BonusBuyTo[]|Collection $bonusBuyTo
     *
     * @return BonusBuyShare
     */
    public function setBonusBuyTo(Collection $bonusBuyTo): BonusBuyShare
    {
        $this->bonusBuyTo = $bonusBuyTo;

        return $this;
    }

    /**
     * @return BonusBuyFrom[]|Collection
     */
    public function getBonusBuyFrom()
    {
        return $this->bonusBuyFrom;
    }

    /**
     * @param BonusBuyFrom[]|Collection $bonusBuyFrom
     *
     * @return BonusBuyShare
     */
    public function setBonusBuyFrom(Collection $bonusBuyFrom): BonusBuyShare
    {
        $this->bonusBuyTo = $bonusBuyFrom;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDelete():bool
    {
        return $this->getAct() === self::ACT_DELETE;
    }
}
