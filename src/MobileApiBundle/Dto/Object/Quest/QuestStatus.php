<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Quest;

use JMS\Serializer\Annotation as Serializer;

class QuestStatus
{
    /**
     * @Serializer\SerializedName("number")
     * @Serializer\Type("int")
     * @var int
     */
    protected $number = 0;

    /**
     * @Serializer\SerializedName("total_count")
     * @Serializer\Type("int")
     * @var int
     */
    protected $totalCount = 0;

    /**
     * @Serializer\SerializedName("prev_tasks")
     * @Serializer\Type("array<boolean>")
     * @var int
     */
    protected $prevTasks = [];

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     * @return QuestStatus
     */
    public function setNumber(int $number): QuestStatus
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     * @return QuestStatus
     */
    public function setTotalCount(int $totalCount): QuestStatus
    {
        $this->totalCount = $totalCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrevTasks(): int
    {
        return $this->prevTasks;
    }

    /**
     * @param int $prevTasks
     * @return QuestStatus
     */
    public function setPrevTasks(int $prevTasks): QuestStatus
    {
        $this->prevTasks = $prevTasks;
        return $this;
    }
}
