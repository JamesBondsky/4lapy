<?php

namespace FourPaws\PersonalBundle\Entity;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Helpers\DateHelper;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderSubscribe extends BaseEntity
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_ORDER_ID")
     * @Serializer\Groups(groups={"create","read"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $orderId;
    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("UF_DATE_CREATE")
     * @Serializer\Groups(groups={"create","read"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateCreate;
    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("UF_DATE_EDIT")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateEdit;
    /**
     * @var Date
     * @Serializer\Type("bitrix_date")
     * @Serializer\SerializedName("UF_DATE_START")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateStart;
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_FREQUENCY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryFrequency;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_DELIVERY_TIME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryTime;
    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("UF_ACTIVE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $active;
    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("UF_NEXT_CHECK")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $nextCheck;

    /** @var  OrderSubscribeService */
    private $orderSubscribeService;
    /** @var  string */
    private $deliveryFrequencyXmlId;
    /** @var  string */
    private $deliveryFrequencyValue;

    /**
     * @return OrderSubscribeService
     * @throws ApplicationCreateException
     */
    protected function getOrderSubscribeService() : OrderSubscribeService
    {
        if (!$this->orderSubscribeService) {
            $appCont = Application::getInstance()->getContainer();
            $this->orderSubscribeService = $appCont->get('order_subscribe.service');
        }

        return $this->orderSubscribeService;
    }

    /**
     * @return int
     */
    public function getOrderId() : int
    {
        return $this->orderId ?? 0;
    }

    /**
     * @param int $orderId
     *
     * @return self
     */
    public function setOrderId(int $orderId) : self
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return null|DateTime
     */
    public function getDateCreate()
    {
        return $this->dateCreate ?? null;
    }

    /**
     * @param null|DateTime|string $dateCreate
     *
     * @return self
     */
    public function setDateCreate($dateCreate) : self
    {
        if ($dateCreate instanceof DateTime) {
            $this->dateCreate = $dateCreate;
        } else {
            if (is_scalar($dateCreate)) {
                $this->dateCreate = new DateTime($dateCreate, 'd.m.Y H:i:s');
            } elseif($dateCreate === null) {
                $this->dateCreate = null;
            }
        }

        return $this;
    }

    /**
     * @return null|DateTime
     */
    public function getDateEdit()
    {
        return $this->dateEdit ?? null;
    }

    /**
     * @param null|DateTime|string $dateEdit
     *
     * @return self
     */
    public function setDateEdit($dateEdit) : self
    {
        if ($dateEdit instanceof DateTime) {
            $this->dateEdit = $dateEdit;
        } else {
            if (is_scalar($dateEdit)) {
                $this->dateEdit = new DateTime($dateEdit, 'd.m.Y H:i:s');
            } elseif($dateEdit === null) {
                $this->dateEdit = null;
            }
        }

        return $this;
    }

    /**
     * @return null|Date
     */
    public function getDateStart()
    {
        return $this->dateStart ?? null;
    }

    /**
     * @param string $format
     * @return string
     */
    public function getDateStartFormatted(string $format = 'd.m.Y') : string
    {
        $date =  $this->getDateStart();

        return $date ? $date->format($format) : '';
    }

    /**
     * @return int
     */
    public function getDateStartWeekday() : int
    {
        $dateStart = $this->getDateStart();
        return $dateStart ? (int)$dateStart->format('N') : 0;
    }

    /**
     * @param bool $lower
     * @param string $case
     * @return string
     */
    public function getDateStartWeekdayRu(bool $lower = true, string $case = '') : string
    {
        $case = $case === '' ? DateHelper::NOMINATIVE : $case;
        $weekDay = $this->getDateStartWeekday();
        $result = $weekDay ? DateHelper::replaceRuDayOfWeek('#'.$weekDay.'#', $case) : '';

        return $lower ? ToLower($result) : $result;
    }

    /**
     * @param null|Date|string $dateStart
     *
     * @return self
     */
    public function setDateStart($dateStart) : self
    {
        if ($dateStart instanceof Date) {
            $this->dateStart = $dateStart;
        } else {
            if (is_scalar($dateStart)) {
                $this->dateStart = new Date($dateStart, 'd.m.Y');
            } else {
                $this->dateStart = null;
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryFrequency() : int
    {
        return $this->deliveryFrequency ?? 0;
    }

    /**
     * @param int $deliveryFrequency
     *
     * @return self
     */
    public function setDeliveryFrequency(int $deliveryFrequency) : self
    {
        $this->deliveryFrequency = (int)$deliveryFrequency;
        unset($this->deliveryFrequencyXmlId);

        return $this;
    }

    /**
     * @return string
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     * @throws ApplicationCreateException
     */
    public function getDeliveryFrequencyXmlId() : string
    {
        if (!isset($this->deliveryFrequencyXmlId)) {
            /** @var OrderSubscribeService $orderSubscribeService */
            $orderSubscribeService = $this->getOrderSubscribeService();
            $this->deliveryFrequencyXmlId = $orderSubscribeService->getFrequencyXmlId(
                $this->getDeliveryFrequency()
            );
        }

        return $this->deliveryFrequencyXmlId;
    }

    /**
     * @return string
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     * @throws ApplicationCreateException
     */
    public function getDeliveryFrequencyValue() : string
    {
        if (!isset($this->deliveryFrequencyValue)) {
            /** @var OrderSubscribeService $orderSubscribeService */
            $orderSubscribeService = $this->getOrderSubscribeService();
            $this->deliveryFrequencyValue = $orderSubscribeService->getFrequencyValue(
                $this->getDeliveryFrequency()
            );
        }

        return $this->deliveryFrequencyValue;
    }

    /**
     * @return string
     */
    public function getDeliveryTime() : string
    {
        return $this->deliveryTime ?? '';
    }

    /**
     * @param string $deliveryTime
     *
     * @return self
     */
    public function setDeliveryTime(string $deliveryTime) : self
    {
        $this->deliveryTime = $deliveryTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryTimeNormalized() : string
    {
        $result = $this->getDeliveryTime();
        // &mdash;, &ndash;
        $result = str_replace(
            ['—', '–'],
            '-',
            $result
        );

        return $result;
    }

    /**
     * Преобразовывает значение вида "09:00-16:00" к виду: "с 9 до 16"
     *
     * @param bool $noBreak
     * @return string
     */
    public function getDeliveryTimeFormattedRu(bool $noBreak = false) : string
    {
        $result = $this->getDeliveryTimeNormalized();
        $pieces = explode('-', $result);
        if (count($pieces) === 2) {
            $from = trim($pieces[0]);
            $to = trim($pieces[1]);
            $timePieces = explode(':', $from);
            if (count($timePieces) === 2 && $timePieces[1] === '00') {
                $from = (int)$timePieces[0];
            }
            $timePieces = explode(':', $to);
            if (count($timePieces) === 2 && $timePieces[1] === '00') {
                $to = (int)$timePieces[0];
            }
            $result = 'с '.$from.' до '.$to;
            if ($noBreak) {
                $result = str_replace(' ', '&nbsp;', $result);
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->active ?? true;
    }

    /**
     * @param bool $active
     *
     * @return self
     */
    public function setActive(bool $active) : self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return null|DateTime
     */
    public function getNextCheck()
    {
        return $this->nextCheck ?? null;
    }

    /**
     * @param null|DateTime|string $nextCheckDate
     *
     * @return self
     */
    public function setNextCheck($nextCheckDate) : self
    {
        if ($nextCheckDate instanceof DateTime) {
            $this->nextCheck = $nextCheckDate;
        } else {
            if (is_scalar($nextCheckDate)) {
                $this->nextCheck = new DateTime($nextCheckDate, 'd.m.Y H:i:s');
            } elseif($nextCheckDate === null) {
                $this->nextCheck = null;
            }
        }

        return $this;
    }

    /**
     * @return \DateTime|null
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getNextDeliveryDate()
    {
        $result = null;
        $baseDateRaw = '';

        $dateStartRaw = $this->getDateStart();
        if ($dateStartRaw) {
            $dateStart = new \DateTime($dateStartRaw);
            $baseDate = new \DateTime($baseDateRaw);
            $intervalPassed = $dateStart->diff($baseDate);
            if ($intervalPassed->invert) {
                // если заданная дата первой доставки является будущей, то она и будет ближайшей датой
                $result = $dateStart;
            } else {
                $periodIntervalSpec = '';

                $frequencyXmlId = $this->getDeliveryFrequencyXmlId();
                switch ($frequencyXmlId) {
                    case 'WEEK_1':
                        // раз в неделю
                        $periodIntervalSpec = 'P1W';
                        break;

                    case 'WEEK_2':
                        // раз в две недели
                        $periodIntervalSpec = 'P2W';
                        break;

                    case 'WEEK_3':
                        // раз в три недели
                        $periodIntervalSpec = 'P3W';
                        break;

                    case 'MONTH_1':
                        // раз в месяц
                        $periodIntervalSpec = 'P1M';
                        break;

                    case 'MONTH_2':
                        // раз в два месяца
                        $periodIntervalSpec = 'P2M';
                        break;

                    case 'MONTH_3':
                        // раз в три месяца
                        $periodIntervalSpec = 'P3M';
                        break;
                }

                if ($periodIntervalSpec) {
                    $periodStart = $dateStart;
                    $periodEnd = clone $baseDate;
                    $periodInterval = new \DateInterval($periodIntervalSpec);
                    // конечная дата периода: +два интервала
                    $periodEnd->add($periodInterval);
                    $periodEnd->add($periodInterval);

                    $period = new \DatePeriod($periodStart, $periodInterval, $periodEnd);
                    foreach($period as $periodDate) {
                        if ($periodDate > $baseDate) {
                            $result = $periodDate;
                            break;
                        }
                    }
                }
            }
        }

        return $result;
    }

}
