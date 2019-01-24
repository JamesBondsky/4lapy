<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

class PersonalBonus
{
    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("amount")
     * @var float
     */
    protected $amount;

    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("total_income")
     * @var float
     */
    protected $totalIncome;

    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("total_outgo")
     * @var float
     */
    protected $totalOutgo;

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("next_stage")
     * @var int
     */
    protected $nextStage;

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return PersonalBonus
     */
    public function setAmount(float $amount): PersonalBonus
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalIncome(): float
    {
        return $this->totalIncome;
    }

    /**
     * @param float $totalIncome
     * @return PersonalBonus
     */
    public function setTotalIncome(float $totalIncome): PersonalBonus
    {
        $this->totalIncome = $totalIncome;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalOutgo(): float
    {
        return $this->totalOutgo;
    }

    /**
     * @param float $totalOutgo
     * @return PersonalBonus
     */
    public function setTotalOutgo(float $totalOutgo): PersonalBonus
    {
        $this->totalOutgo = $totalOutgo;
        return $this;
    }

    /**
     * @return int
     */
    public function getNextStage(): int
    {
        return $this->nextStage;
    }

    /**
     * @param int $nextStage
     * @return PersonalBonus
     */
    public function setNextStage(int $nextStage): PersonalBonus
    {
        $this->nextStage = $nextStage;
        return $this;
    }
}
