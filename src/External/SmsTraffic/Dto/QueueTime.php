<?php

namespace FourPaws\External\SmsTraffic\Dto;

class QueueTime
{
    /**
     * @var \DateTime
     */
    protected $from;

    /**
     * @var \DateTime
     */
    protected $to;

    /**
     * @return \DateTime
     */
    public function getFrom(): \DateTime
    {
        return $this->from;
    }

    /**
     * @param \DateTime $from
     * @return QueueTime
     */
    public function setFrom(\DateTime $from): QueueTime
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTo(): \DateTime
    {
        return $this->to;
    }

    /**
     * @param \DateTime $to
     * @return QueueTime
     */
    public function setTo(\DateTime $to): QueueTime
    {
        $this->to = $to;

        return $this;
    }
}
