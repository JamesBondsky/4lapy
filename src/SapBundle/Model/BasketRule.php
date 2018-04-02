<?php
/**
 * Created by PhpStorm.
 * Date: 27.03.2018
 * Time: 18:06
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SapBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 *
 * Class DiscountRule
 * @package FourPaws\SapBundle\Model
 */
class BasketRule
{
    protected const DEFAULT_CONDITIONS = [
        'CLASS_ID' => 'CondGroup',
        'DATA' => [
            'All' => 'AND',
            'True' => 'True',
        ],
        'CHILDREN' => []
    ];

    /**
     * @var integer
     * @Serializer\SkipWhenEmpty()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("ID")
     */
    protected $id;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("LID")
     */
    protected $lid = SITE_ID;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     */
    protected $name;

    /**
     * @var \DateTime
     * @Serializer\Type("bitrix_date_time_object")
     * @Serializer\SerializedName("ACTIVE_FROM")
     */
    protected $activeFrom;

    /**
     * @var \DateTime
     * @Serializer\Type("bitrix_date_time_object")
     * @Serializer\SerializedName("ACTIVE_TO")
     */
    protected $activeTo;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("ACTIVE")
     */
    protected $active = true;

    /**
     * @var integer
     * @Serializer\Type("int")
     * @Serializer\SerializedName("SORT")
     */
    protected $sort = 100;

    /**
     * @var integer
     * @Serializer\Type("int")
     * @Serializer\SerializedName("PRIORITY")
     */
    protected $priority = 1;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("LAST_DISCOUNT")
     */
    protected $lastDiscount = false;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("LAST_LEVEL_DISCOUNT")
     */
    protected $lastLevelDiscount = false;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("XML_ID")
     */
    protected $xmlId;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\SerializedName("CONDITIONS")
     */
    protected $conditions = self::DEFAULT_CONDITIONS;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\SerializedName("ACTIONS")
     */
    protected $actions;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\SerializedName("USER_GROUPS")
     */
    protected $userGroups;

    /**
     * @return int
     */
    public function getId(): ? int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return BasketRule
     */
    public function setId(?int $id): BasketRule
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLid(): string
    {
        return $this->lid;
    }

    /**
     * @param string $lid
     *
     * @return BasketRule
     */
    public function setLid(?string $lid): BasketRule
    {
        $this->lid = $lid ?? SITE_ID;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ? string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return BasketRule
     */
    public function setName(?string $name): BasketRule
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getActiveFrom(): ? \DateTime
    {
        return $this->activeFrom;
    }

    /**
     * @param \DateTime $activeFrom
     *
     * @return BasketRule
     */
    public function setActiveFrom(?\DateTime $activeFrom): BasketRule
    {
        $this->activeFrom = $activeFrom;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getActiveTo(): ? \DateTime
    {
        return $this->activeTo;
    }

    /**
     * @param \DateTime $activeTo
     *
     * @return BasketRule
     */
    public function setActiveTo(?\DateTime $activeTo): BasketRule
    {
        $this->activeTo = $activeTo;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->active;
    }

    /**
     * @param bool $active
     *
     * @return BasketRule
     */
    public function setActive(?bool $active): BasketRule
    {
        $this->active = (bool)$active;
        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): ? int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     *
     * @return BasketRule
     */
    public function setSort(?int $sort): BasketRule
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return BasketRule
     */
    public function setPriority(int $priority): BasketRule
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLastDiscount(): bool
    {
        return $this->lastDiscount;
    }

    /**
     * @param bool $lastDiscount
     *
     * @return BasketRule
     */
    public function setLastDiscount(bool $lastDiscount): BasketRule
    {
        $this->lastDiscount = $lastDiscount;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLastLevelDiscount(): bool
    {
        return $this->lastLevelDiscount;
    }

    /**
     * @param bool $lastLevelDiscount
     *
     * @return BasketRule
     */
    public function setLastLevelDiscount(bool $lastLevelDiscount): BasketRule
    {
        $this->lastLevelDiscount = $lastLevelDiscount;
        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->xmlId;
    }

    /**
     * @param string $xmlId
     *
     * @return BasketRule
     */
    public function setXmlId(string $xmlId): BasketRule
    {
        $this->xmlId = $xmlId;
        return $this;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     *
     * @return BasketRule
     */
    public function setConditions(array $conditions): BasketRule
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     *
     * @return BasketRule
     */
    public function setActions(array $actions): BasketRule
    {
        $this->actions = $actions;
        return $this;
    }

    /**
     * @return array
     */
    public function getUserGroups(): array
    {
        return $this->userGroups;
    }

    /**
     * @param array $userGroups
     *
     * @return BasketRule
     */
    public function setUserGroups(array $userGroups): BasketRule
    {
        $this->userGroups = $userGroups;
        return $this;
    }
}