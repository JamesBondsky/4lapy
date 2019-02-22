<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 22.02.2019
 * Time: 11:19
 */

namespace FourPaws\StoreBundle\Entity;

use Bitrix\Main\DB\Exception;
use Dadata\Response\Date;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Exception\NotFoundException;

/**
 * Класс для работы с днями формирования заказа в расписаниях поставок
 *
 * Class OrderDay
 * @package FourPaws\StoreBundle\Entity
 *
 */
class OrderDay
{
    /**
     * Номер недели дня заказа
     *
     * @var int
     */
    protected $orderDay;

    /**
     * Номер недели дня поставки
     *
     * @var int
     */
    protected $supplyDay;

    /**
     * Номер недели для которого активны дни заказа
     *
     * @var int
     */
    protected $weekNum;

    /**
     * @return int
     */
    public function getWeekNum(): int
    {
        return $this->weekNum;
    }

    /**
     * @param int $weekNum
     */
    public function setWeekNum(int $weekNum): OrderDay
    {
        $this->weekNum = $weekNum;

        return $this;
    }


    /**
     * OrderDay constructor.
     * @param int $orderDay
     * @param int $supplyDay
     * @param int $scheduleType
     */
    public function __construct(int $orderDay, int $supplyDay, int $scheduleType, \DateTime $from)
    {
        $this->setOrderDay($orderDay);
        $this->setSupplyDay($supplyDay);

        $weekNum = (int)$from->format('W');

        if($scheduleType == DeliverySchedule::TYPE_BY_WEEK && $orderDay > $supplyDay){
            $weekNum -= 1;
        }

        $this->setWeekNum($weekNum);

    }

    /**
     * @return int
     */
    public function getOrderDay()
    {
        return $this->orderDay;
    }

    /**
     * @param int $orderDay
     */
    public function setOrderDay($orderDay): OrderDay
    {
        $this->orderDay = $orderDay;

        return $this;
    }

    /**
     * @return int
     */
    public function getSupplyDay()
    {
        return $this->supplyDay;
    }

    /**
     * @param int $supplyDay
     */
    public function setSupplyDay($supplyDay): OrderDay
    {
        $this->supplyDay = $supplyDay;

        return $this;
    }


}