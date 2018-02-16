<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Entity;

class CardBonus
{
    /**
     * id карты
     *
     * @var string
     */
    private $cardId = 0;
    
    /**
     * номер карты
     *
     * @var string
     */
    private $cardNumber = '0000000000000';
    
    /**
     * текущие бонусы
     *
     * @var float
     */
    private $activeBalance = 0;
    
    /**
     * все бонусы
     *
     * @var float
     */
    private $balance = 0;
    
    /**
     * скидка по карте - не верная - из манзаны
     *
     * @var float
     */
    private $discount = 0;
    
    /**
     * Сумма покупок
     *
     * @var float
     */
    private $sum = 0;
    
    /**
     *Потраченные бонусы
     *
     * @var float
     */
    private $credit = 0;
    
    /**
     *Полученные бонусы
     *
     * @var float
     */
    private $debit = 0;
    
    /** @var bool */
    private $empty = true;
    
    /** @var float */
    private $realDiscount = 0;
    
    /** @var float */
    private $sumToNext = 0;
    
    /** @var bool */
    private $real = false;
    
    /**
     * @return string
     */
    public function getCardId() : string
    {
        return $this->cardId ?? '';
    }
    
    /**
     * @param string $cardId
     */
    public function setCardId(string $cardId)
    {
        $this->cardId = $cardId;
    }
    
    /**
     * @return string
     */
    public function getFormatedCardNumber() : string
    {
        return !empty($this->getCardNumber()) ? vsprintf(
            '%s%s%s%s%s %s%s%s %s%s %s%s%s',
            str_split($this->getCardNumber())
        ) : '';
    }
    
    /**
     * @return string
     */
    public function getCardNumber() : string
    {
        return $this->cardNumber ?? '0000000000000';
    }
    
    /**
     * @param string $cardNumber
     */
    public function setCardNumber(string $cardNumber)
    {
        $this->cardNumber = $cardNumber;
    }
    
    /**
     * @return float
     */
    public function getActiveBalance() : float
    {
        return $this->activeBalance ?? 0;
    }
    
    /**
     * @param float $activeBalance
     */
    public function setActiveBalance(float $activeBalance)
    {
        $this->activeBalance = $activeBalance;
    }
    
    /**
     * @return float
     */
    public function getBalance() : float
    {
        return $this->balance ?? 0;
    }
    
    /**
     * @param float $balance
     */
    public function setBalance(float $balance)
    {
        $this->balance = $balance;
    }
    
    /**
     * @return float
     */
    public function getDiscount() : float
    {
        return $this->discount ?? 0;
    }
    
    /**
     * @param float $discount
     */
    public function setDiscount(float $discount)
    {
        $this->discount = $discount;
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
     */
    public function setCredit(float $credit)
    {
        $this->credit = $credit ?? 0;
    }
    
    /**
     * @return float
     */
    public function getDebit() : float
    {
        return $this->debit;
    }
    
    /**
     * @param float $debit
     */
    public function setDebit(float $debit)
    {
        $this->debit = $debit ?? 0;
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
     */
    public function setEmpty(bool $empty)
    {
        $this->empty = $empty;
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
     */
    public function setSumToNext(float $sumToNext)
    {
        $this->sumToNext = $sumToNext;
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
            $discountTable = UserBonus::$discountTable;
            $finalSum      = end($discountTable);
            if ($sum < $finalSum) {
                $reverse          = array_reverse(UserBonus::$discountTable, true);
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
        
        return $sumToNext;
    }
    
    /**
     * @return float
     */
    public function getRealDiscount() : float
    {
        if ($this->realDiscount <= 0) {
            $this->realDiscount = $this->getGeneratedRealDiscount();
        }
        
        return $this->realDiscount ?? 0;
    }
    
    /**
     * @param float $realDiscount
     */
    public function setRealDiscount(float $realDiscount)
    {
        $this->realDiscount = $realDiscount;
    }
    
    /**
     * @return float
     */
    public function getGeneratedRealDiscount() : float
    {
        $discount = 0;
        $sum      = $this->getSum();
        if ($sum > 0) {
            $reverse = array_reverse(UserBonus::$discountTable, true);
            foreach ($reverse as $discountPercent => $minSum) {
                if ($sum >= $minSum) {
                    $discount = $discountPercent;
                    break;
                }
            }
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
     */
    public function setSum(float $sum)
    {
        $this->sum = $sum;
    }
    
    /**
     * @return bool
     */
    public function isReal() : bool
    {
        return $this->real ?? false;
    }
    
    /**
     * @param bool $real
     */
    public function setReal(bool $real)
    {
        $this->real = $real;
    }
}
