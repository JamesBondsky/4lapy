<?php

namespace FourPaws\StoreBundle\Entity;

use Bitrix\Main\UserFieldTable;
use FourPaws\App\Application;
use FourPaws\AppBundle\Collection\UserFieldEnumCollection;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\AppBundle\Service\UserFieldEnumService;
use FourPaws\Enum\HlblockCode;
use FourPaws\Helpers\HighloadHelper;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use WebArch\BitrixCache\BitrixCache;

class ScheduleResult
{
    public const RESULT_ERROR = -1;

    public const DATE_ACTIVE_FORMAT = 'd.m.Y';

    public const DEFUALT_SCHED_TYPE = 'Z1';

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $id;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_RECEIVER")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $receiverCode;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_SENDER")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $senderCode;

    /**
     * @var array
     * @Serializer\Type("array_or_false<string>")
     * @Serializer\SerializedName("UF_ROUTE")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $routeCodes;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_DAYS_11")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $days11 = -1;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_DAYS_13")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $days13 = -1;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_DAYS_18")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $days18 = -1;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_DAYS_21")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $days21 = -1;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_DAYS_24")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $days24 = -1;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_DATE_ACTIVE")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $dateActive;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_REGULARITY")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $regularity;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return ScheduleResult
     */
    public function setId(int $id): ScheduleResult
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiverCode(): string
    {
        return $this->receiverCode;
    }

    /**
     * @param string $receiverCode
     *
     * @return ScheduleResult
     */
    public function setReceiverCode(string $receiverCode): ScheduleResult
    {
        $this->receiverCode = $receiverCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSenderCode(): string
    {
        return $this->senderCode;
    }

    /**
     * @param string $senderCode
     *
     * @return ScheduleResult
     */
    public function setSenderCode(string $senderCode): ScheduleResult
    {
        $this->senderCode = $senderCode;

        return $this;
    }

    /**
     * @return array
     */
    public function getRouteCodes(): array
    {
        return $this->routeCodes;
    }

    /**
     * @param array $routeCodes
     *
     * @return ScheduleResult
     */
    public function setRouteCodes(array $routeCodes): ScheduleResult
    {
        $this->routeCodes = $routeCodes;
        $this->route = null;

        return $this;
    }

    /**
     * @return int
     */
    public function getDays11(): int
    {
        return $this->days11 ?? static::RESULT_ERROR;
    }

    /**
     * @param int $days11
     *
     * @return ScheduleResult
     */
    public function setDays11(int $days11): ScheduleResult
    {
        $this->days11 = $days11;

        return $this;
    }

    /**
     * @return int
     */
    public function getDays13(): int
    {
        return $this->days13 ?? static::RESULT_ERROR;
    }

    /**
     * @param int $days13
     *
     * @return ScheduleResult
     */
    public function setDays13(int $days13): ScheduleResult
    {
        $this->days13 = $days13;

        return $this;
    }

    /**
     * @return int
     */
    public function getDays18(): int
    {
        return $this->days18 ?? static::RESULT_ERROR;
    }

    /**
     * @param int $days18
     *
     * @return ScheduleResult
     */
    public function setDays18(int $days18): ScheduleResult
    {
        $this->days18 = $days18;

        return $this;
    }

    /**
     * @return int
     */
    public function getDays24(): int
    {
        return $this->days24 ?? static::RESULT_ERROR;
    }

    /**
     * @param int $days24
     *
     * @return ScheduleResult
     */
    public function setDays24(int $days24): ScheduleResult
    {
        $this->days24 = $days24;

        return $this;
    }

    /**
     * @todo move to service
     *
     * @param \DateTime $for
     *
     * @return int
     */
    public function getDays(\DateTime $for): int
    {
        $h = (int)$for->format('G');
        $result = static::RESULT_ERROR;
        switch (true) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case ($h < 11):
                $result = $this->getDays11();
            /** @noinspection PhpMissingBreakStatementInspection */
            case ($h < 13):
                /** @noinspection SuspiciousAssignmentsInspection */
                $result = ($result === static::RESULT_ERROR) ? $this->getDays13() : $result;
            /** @noinspection PhpMissingBreakStatementInspection */
            case ($h < 18):
                /** @noinspection SuspiciousAssignmentsInspection */
                $result = ($result === static::RESULT_ERROR) ? $this->getDays18() : $result;
            default:
                /** @noinspection SuspiciousAssignmentsInspection */
                $result = ($result === static::RESULT_ERROR) ? $this->getDays24() : $result;
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getDateActive(): ?string
    {
        return $this->dateActive;
    }

    /**
     * @param string $dateActive
     * @return ScheduleResult
     */
    public function setDateActive(string $dateActive): ScheduleResult
    {
        $this->dateActive = $dateActive;
        return $this;
    }

    /**
     * @param string $regularity
     * @return ScheduleResult
     */
    public function setRegularity(string $regularity): ScheduleResult
    {
        $this->regularity = $regularity;
        return $this;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getRegularity(): ?string
    {
        if($this->regularity === null){
            $this->setRegularity($this->getDefaultRegularity());
        }
        return $this->regularity;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getRegularityName(): ?string
    {
        $id = $this->getRegularity();
        $regularities = $this->getRegularityEnum();
        $regularity = $regularities->get($id);
        return $regularity->getValue() ?: '';
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getRegularitySort(): ?string
    {
        $id = $this->getRegularity();
        $regularities = $this->getRegularityEnum();
        $regularity = $regularities->get($id);
        return $regularity->getSort() ?: 500;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getDefaultRegularity()
    {
        $regularities = $this->getRegularityEnum();
        $regularities = $regularities->filter(function($item){
            return $item->getXmlId() == self::DEFUALT_SCHED_TYPE;
        });
        return $regularities->first()->getId();
    }

    /**
     * @return UserFieldEnumCollection
     * @throws \Exception
     */
    public function getRegularityEnum()
    {
        $getRegularities  = function() {
            /** @var UserFieldEnumService $userFieldEnumService */
            $userFieldEnumService = Application::getInstance()->getContainer()->get('userfield_enum.service');
            $userFieldId = UserFieldTable::query()->setSelect(['ID', 'XML_ID'])->setFilter(
                [
                    'FIELD_NAME' => 'UF_REGULARITY',
                    'ENTITY_ID' => 'HLBLOCK_' . HighloadHelper::getIdByName(HlblockCode::DELIVERY_SCHEDULE_RESULT),
                ]
            )->exec()->fetch()['ID'];
            $regularities = $userFieldEnumService->getEnumValueCollection($userFieldId);
            return $regularities;
        };
        /** @var UserFieldEnumCollection $regularities */
        $regularities = (new BitrixCache())
                            ->withId(__METHOD__)
                            ->withTag('delivery_schedule_regularity')
                            ->withTime(86400*356)
                            ->resultOf($getRegularities)['result'];

        return $regularities;
    }

    /**
     * @param int $days21
     * @return ScheduleResult
     */
    public function setDays21(int $days21): ScheduleResult
    {
        $this->days21 = $days21;
        return $this;
    }

    /**
     * @return int
     */
    public function getDays21(): int
    {
        return $this->days21 ?? static::RESULT_ERROR;
    }

}
