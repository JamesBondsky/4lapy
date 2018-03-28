<?php

namespace FourPaws\PersonalBundle\Entity;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\AppBundle\Service\UserFieldEnumService;
use FourPaws\Helpers\DateHelper;
use FourPaws\PersonalBundle\Exception\RuntimeException;
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
     * @Serializer\SkipWhenEmpty()
     * @Assert\NotBlank(groups={"create","read"})
     */
    protected $orderId;
    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_CREATE")
     * @Serializer\Groups(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     * @Assert\NotBlank(groups={"create","read"})
     */
    protected $dateCreate;
    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_EDIT")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     * @Assert\NotBlank(groups={"create","read","update"})
     */
    protected $dateEdit;
    /**
     * @var Date
     * @Serializer\Type("bitrix_date_ex")
     * @Serializer\SerializedName("UF_DATE_START")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     * @Assert\NotBlank(groups={"create","read"})
     */
    protected $dateStart;
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_FREQUENCY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     * @Assert\NotBlank(groups={"create","read","update"})
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
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_LAST_CHECK")
     * @Serializer\Groups(groups={"create","read","update"})
     */
    protected $lastCheck;

    /** @var OrderSubscribeService */
    private $orderSubscribeService;
    /** @var UserFieldEnumService */
    private $userFieldEnumService;
    /** @var null|Order */
    private $order;
    /** @var UserFieldEnumValue */
    private $deliveryFrequencyEntity;

    /**
     * @return array
     */
    public function getAllFields() : array
    {
        $fields = [
            'ID' => $this->getId(),
            'UF_ORDER_ID' => $this->getOrderId(),
            'UF_DATE_CREATE' => $this->getDateCreate(),
            'UF_DATE_EDIT' => $this->getDateEdit(),
            'UF_DATE_START' => $this->getDateStart(),
            'UF_FREQUENCY' => $this->getDeliveryFrequency(),
            'UF_DELIVERY_TIME' => $this->getDeliveryTime(),
            'UF_ACTIVE' => $this->isActive(),
            'UF_LAST_CHECK' => $this->getLastCheck(),
        ];

        return $fields;
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
        if (isset($this->order)) {
            unset($this->order);
        }

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
        $this->dateCreate = $this->processDateTimeValue($dateCreate);

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
        $this->dateEdit = $this->processDateTimeValue($dateEdit);

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
     * @param null|Date|string $dateStart
     *
     * @return self
     */
    public function setDateStart($dateStart) : self
    {
        $this->dateStart = $this->processDateValue($dateStart);

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
        if (isset($this->deliveryFrequencyEntity)) {
            unset($this->deliveryFrequencyEntity);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryTime() : string
    {
        $value = $this->deliveryTime ? str_replace(' ', '', $this->deliveryTime) : '';

        return $value;
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
    public function getLastCheck()
    {
        return $this->lastCheck ?? null;
    }

    /**
     * @param null|DateTime|string $lastCheckDate
     *
     * @return self
     */
    public function setLastCheck($lastCheckDate) : self
    {
        $this->lastCheck = $this->processDateTimeValue($lastCheckDate);

        return $this;
    }

    /**
     * @param string $baseDateValue Базовая дата для расчета в формате d.m.Y
     * @return \DateTime
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws \Exception
     */
    public function getNextDeliveryDate(string $baseDateValue = '') : \DateTime
    {
        $dateStartRaw = $this->getDateStart();
        if (!$dateStartRaw) {
            throw new RuntimeException('Дата первой поставки не задана', 100);
        }

        $result = null;

        // принудительное приведение к требуемому формату - время нам здесь не нужно
        $baseDateValue = (new \DateTime($baseDateValue))->format('d.m.Y');

        $dateStart = new \DateTime($dateStartRaw->format('d.m.Y'));
        $baseDate = new \DateTime($baseDateValue);
        $intervalPassed = $dateStart->diff($baseDate);
        if ($intervalPassed->invert) {
            // если заданная дата первой доставки еще не наступила, то она и будет ближайшей датой
            $result = $dateStart;
        } else {
            $periodIntervalSpec = '';
            $frequencyXmlId = $this->getDeliveryFrequencyEntity()->getXmlId();
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
                    // раз в месяц (каждые 4 недели)
                    $periodIntervalSpec = 'P4W';
                    break;

                case 'MONTH_2':
                    // раз в два месяца (каждые 8 недель)
                    $periodIntervalSpec = 'P8W';
                    break;

                case 'MONTH_3':
                    // раз в три месяца (каждые 12 недель)
                    $periodIntervalSpec = 'P12W';
                    break;
            }

            if (!$periodIntervalSpec) {
                throw new RuntimeException('Не удалось определить периодичность доставки', 200);
            }

            $periodStart = $dateStart;
            $periodEnd = clone $baseDate;
            $periodInterval = new \DateInterval($periodIntervalSpec);
            // конечная дата периода: +два интервала
            $periodEnd->add($periodInterval);
            $periodEnd->add($periodInterval);

            $period = new \DatePeriod($periodStart, $periodInterval, $periodEnd);
            foreach($period as $periodDate) {
                /** @var \DateTime $periodDate */
                if ($periodDate >= $baseDate) {
                    $result = new \DateTime($periodDate->format('d.m.Y'));
                    break;
                }
            }
        }

        if (!$result) {
            throw new RuntimeException('Не удалось определить дату доставки', 300);
        }

        return $result;
    }

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
     * @return UserFieldEnumService
     * @throws ApplicationCreateException
     */
    protected function getUserFieldEnumService() : UserFieldEnumService
    {
        if (!$this->userFieldEnumService) {
            $appCont = Application::getInstance()->getContainer();
            $this->userFieldEnumService = $appCont->get('userfield_enum.service');
        }

        return $this->userFieldEnumService;
    }

    /**
     * @return Order|null
     * @throws ApplicationCreateException
     * @throws \Exception
     */
    public function getOrder()
    {
        if (!isset($this->order)) {
            $this->order = $this->getOrderSubscribeService()->getOrderById(
                $this->getOrderId()
            );
        }

        return $this->order;
    }

    /**
     * @return UserFieldEnumValue
     * @throws ApplicationCreateException
     * @throws \Exception
     */
    public function getDeliveryFrequencyEntity()
    {
        if (!isset($this->deliveryFrequencyEntity)) {
            $this->deliveryFrequencyEntity = $this->getUserFieldEnumService()->getEnumValueEntity(
                $this->getDeliveryFrequency()
            );
        }

        return $this->deliveryFrequencyEntity;
    }

    /**
     * @param $value
     * @return Date|null|string
     */
    protected function processDateValue($value)
    {
        if (!($value instanceof Date)) {
            if (is_scalar($value) && $value !== '') {
                $value = new Date($value, 'd.m.Y');
            } elseif ($value === '' || $value === false) {
                $value = '';
            } else {
                $value = null;
            }
        }

        return $value;
    }

    /**
     * @param $value
     * @return DateTime|null|string
     */
    protected function processDateTimeValue($value)
    {
        if (!($value instanceof DateTime)) {
            if ($value === '' || $value === false) {
                $value = '';
            } elseif (is_string($value) && $value !== '') {
                $value = new DateTime($value, 'd.m.Y H:i:s');
            } else {
                $value = null;
            }
        }

        return $value;
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
}
