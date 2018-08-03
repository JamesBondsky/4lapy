<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Entity;

class UserBonus
{
    
    public static $discountTable = [
        3 => 0,
        4 => 9000,
        5 => 19000,
        6 => 39000,
        7 => 59000,
    ];
    
    /**
     * текущие бонусы
     *
     * @var float
     */
    private $activeBonus = 0;
    
    /**
     * общее количество бонусов
     *
     * @var float
     */
    private $allBonus = 0;
    
    /**
     * потраченные бонусы
     *
     * @var float
     */
    private $credit = 0;
    
    /**
     * полученные бонусы
     *
     * @var float
     */
    private $debit = 0;
    
    /**
     * сумма покупок
     *
     * @var float
     */
    private $sum = 0;
    
    /**
     * скидка по карте - не верная - из манзаны
     *
     * @var float
     */
    private $discount = 0;
    
    /**
     * сумма до следующей скидки
     *
     * @var float
     */
    private $sumToNext = 0;
    
    /** @var float */
    private $nextDiscount = 0;
    
    /** @var bool */
    private $empty = true;
    
    /**
     * активная карта
     *
     * @var CardBonus
     */
    private $card;
    
    /**
     * реальная скидка
     *
     * @var int
     */
    private $realDiscount = 0;
    
    /** @var int */
    private $progress = 0;
    private $startDiscount;
    private $endDiscount;
    private $startSum;
    private $endSum;

    /**
     * @return float
     */
    public function getActiveBonus() : float
    {
        return $this->activeBonus ?? 0;
    }

    /**
     * @param float $activeBonus
     *
     * @return UserBonus
     */
    public function setActiveBonus(float $activeBonus): self
    {
        $this->activeBonus = $activeBonus;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getAllBonus() : float
    {
        return $this->allBonus ?? 0;
    }

    /**
     * @param float $allBonus
     *
     * @return UserBonus
     */
    public function setAllBonus(float $allBonus): self
    {
        $this->allBonus = $allBonus;
        return $this;
    }
    
    /**
     * @return CardBonus
     */
    public function getCard():CardBonus
    {
        return $this->card ?? new CardBonus();
    }

    /**
     * @param CardBonus $card
     *
     * @return UserBonus
     */
    public function setCard(CardBonus $card): self
    {
        $this->card = $card;
        return $this;
    }

    /**
     * @return bool
     */
    public function haveCard(): bool
    {
        return !empty($this->getCard()->getCardId());
    }
    
    /**
     * @return float
     */
    public function getCredit() : float
    {
        return $this->credit ?? 0;
    }

    /**
     * @param float $credit
     *
     * @return UserBonus
     */
    public function setCredit(float $credit): self
    {
        $this->credit = $credit;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getDebit() : float
    {
        return $this->debit ?? 0;
    }

    /**
     * @param float $debit
     *
     * @return UserBonus
     */
    public function setDebit(float $debit): self
    {
        $this->debit = $debit;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->empty;
    }

    /**
     * @param bool $empty
     *
     * @return UserBonus
     */
    public function setEmpty(bool $empty): self
    {
        $this->empty = $empty;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getDiscount() : float
    {
        return $this->discount ?? 0;
    }

    /**
     *
     * @param float $discount
     *
     * @return UserBonus
     */
    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getSumToNext() : float
    {
        if ($this->sumToNext <= 0) {
            $this->sumToNext = $this->getGeneratedSumToNext();
        }
        
        return $this->sumToNext ?? 0;
    }

    /**
     * @param float $sumToNext
     *
     * @return UserBonus
     */
    public function setSumToNext(float $sumToNext): self
    {
        $this->sumToNext = $sumToNext;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getGeneratedSumToNext() : float
    {
        $sumToNext    = 0;
        $realDiscount = $this->getRealDiscount();
        if ($realDiscount > 0) {
            $sum           = $this->getSum();
            $finalSum      = $this->getEndSum();
            if ($sum < $finalSum) {
                $reverse          = array_reverse(static::$discountTable, true);
                $nextSumCondition = $finalSum;
                foreach ($reverse as $discountPercent => $minSum) {
                    if ($sum < $minSum) {
                        $nextSumCondition = $minSum;
                    }
                    if ($sum >= $minSum) {
                        break;
                    }
                }
                $sumToNext = $nextSumCondition - $sum;
            }
        }
        else{
            $discountTable = static::$discountTable;
            $sumToNext = next($discountTable);
        }
        
        return $sumToNext;
    }
    
    /**
     * @return int
     */
    public function getRealDiscount() : int
    {
        if ($this->realDiscount <= 0) {
            $discountByManzana = $this->getCard()->getDiscount();

            if($discountByManzana > $this->getEndDiscount() || $discountByManzana < $this->getStartDiscount()){
                $this->realDiscount = (int)$discountByManzana;
            }
            else {
                $this->realDiscount = $this->getGeneratedRealDiscount();
            }
        }

        return $this->realDiscount ?? $this->getStartDiscount();
    }

    /**
     * @param int $realDiscount
     *
     * @return UserBonus
     */
    public function setRealDiscount(int $realDiscount): self
    {
        $this->realDiscount = $realDiscount;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getGeneratedRealDiscount() : int
    {
        $discount = 0;
        $sum      = $this->getSum();
        if ($sum > 0) {
            $reverse = array_reverse(static::$discountTable, true);
            foreach ($reverse as $discountPercent => $minSum) {
                if ($sum >= $minSum) {
                    $discount = $discountPercent;
                    break;
                }
            }
        }
        /** установка скидки если есть карта и она учавствует в бонусной программе*/
        if($discount === 0 && !$this->isEmpty() && !$this->getCard()->isEmpty()) {
            $discount = $this->getStartDiscount();
        }
        
        return $discount;
    }
    
    /**
     * @return float
     */
    public function getSum() : float
    {
        return $this->sum ?? 0;
    }

    /**
     * @param float $sum
     *
     * @return UserBonus
     */
    public function setSum(float $sum): self
    {
        $this->sum = $sum;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getNextDiscount() : float
    {
        if ((int)$this->nextDiscount <= 0) {
            $this->nextDiscount = $this->getGeneratedNextDiscount();
        }
        
        return $this->nextDiscount;
    }

    /**
     * @param mixed $nextDiscount
     *
     * @return UserBonus
     */
    public function setNextDiscount($nextDiscount): self
    {
        $this->nextDiscount = $nextDiscount;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getGeneratedNextDiscount() : float
    {
        $sum = $this->getSum();
        
        $discountTable = static::$discountTable;
        end($discountTable);
        $nextDiscount = key($discountTable);
        $reverse      = array_reverse(static::$discountTable, true);
        foreach ($reverse as $discountPercent => $minSum) {
            if ($sum < $minSum) {
                $nextDiscount = $discountPercent;
            }
            if ($sum >= $minSum) {
                break;
            }
        }
        
        return $nextDiscount;
    }
    
    /**
     * @return int
     */
    public function getProgress() : int
    {
        if ($this->progress <= 0) {
            $progress = 0;
            $minDiscount        = $this->getRealDiscount();
            if($minDiscount > 0) {
                $discountTable = static::$discountTable;
                $percentOneInterval = round(100 / (\count($discountTable) - 1), 2);
                $activeIntervals = -1;
                $beginDiscount = $this->getStartDiscount();
                $endDiscount = $this->getEndDiscount();
                reset($discountTable);
                if ($minDiscount < $endDiscount) {
                    $minPrice = 0;
                    $nextPrice = 0;
                    foreach ($discountTable as $discount => $price) {
                        if ($minDiscount >= $discount) {
                            $activeIntervals++;
                            if ($discount > $beginDiscount) {
                                next($discountTable);
                            }
                        }
                        if ($minDiscount === $discount) {
                            $minPrice = $price;
                            $nextPrice = next($discountTable);
                            break;
                        }
                    }
                    /** формула
                     * прогресс = активные интервалы * процент одного интервала + ((((сумма покупок - сумма нижней границы) / (сумма верхней границы - сумма нижней границы))*100)* процент одного интервала / 100)*/
                    $progress = floor(
                        $activeIntervals * $percentOneInterval + ((($this->getSum() - $minPrice) / ($nextPrice - $minPrice) * 100) * $percentOneInterval / 100)
                    );
                } else {
                    $progress = 100;
                }
            }
            $this->progress = $progress;
        }
        
        return $this->progress;
    }

    /**
     * @return int
     */
    public function getStartDiscount(): int
    {
        if($this->startDiscount === null) {
            $discountTable = static::$discountTable;
            reset($discountTable);
            $this->startDiscount = (int)key($discountTable);
        }
        return $this->startDiscount ?? 0;
    }

    /**
     * @return int
     */
    public function getEndDiscount(): int
    {
        if($this->endDiscount === null) {
            $discountTable = static::$discountTable;
            end($discountTable);
            $this->endDiscount = (int)key($discountTable);
        }
        return $this->endDiscount ?? 0;
    }

    /**
     * @return int
     */
    public function getStartSum(): int
    {
        if($this->startSum === null) {
            $discountTable = static::$discountTable;
            reset($discountTable);
            $this->startSum = (int)current($discountTable);
        }
        return $this->startSum ?? 0;
    }

    /**
     * @return int
     */
    public function getEndSum(): int
    {
        if($this->endSum === null) {
            $discountTable = static::$discountTable;
            $this->endSum = (int)end($discountTable);
        }
        return $this->endSum ?? 0;
    }
}
