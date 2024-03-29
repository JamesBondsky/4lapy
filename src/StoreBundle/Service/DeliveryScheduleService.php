<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\UserFieldTable;
use FourPaws\App\Application;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\AppBundle\Service\UserFieldEnumService;
use FourPaws\Enum\HlblockCode;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Collection\OrderDayCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Entity\OrderDay;
use FourPaws\StoreBundle\Exception\BitrixRuntimeException;
use FourPaws\StoreBundle\Exception\ConstraintDefinitionException;
use FourPaws\StoreBundle\Exception\InvalidIdentifierException;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\ValidationException;
use FourPaws\StoreBundle\Repository\DeliveryScheduleRepository;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use WebArch\BitrixCache\BitrixCache;

class DeliveryScheduleService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const TYPE_FIELD_CODE = 'UF_TPZ_TYPE';

    /** @var array */
    protected $weekdays = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        7 => 'sunday',
    ];

    /**
     * Регулярности
     * @var array
     */
    protected $regular;

    /** @var DeliveryScheduleRepository */
    protected $repository;

    public function __construct(DeliveryScheduleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     *
     * @return DeliveryScheduleCollection
     */
    public function findAll(): DeliveryScheduleCollection
    {
        $getSchedules = function () {
            return ['result' => $this->repository->findBy()];
        };

        try {
            /** @var DeliveryScheduleCollection $schedules */
            $schedules = (new BitrixCache())
                ->withTag('delivery_schedule_all')
                ->resultOf($getSchedules)['result'];
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get delivery schedules: %s', $e->getMessage())
            );

            $schedules = new DeliveryScheduleCollection();
        }

        return $schedules;
    }

    /**
     * @param DeliverySchedule $result
     *
     * @return bool
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ValidationException
     */
    public function updateResult(DeliverySchedule $result): bool
    {
        return $this->repository->update($result);
    }

    /**
     * @param Store           $receiver
     * @param StoreCollection $senders
     *
     * @return DeliveryScheduleCollection
     */
    public function findByReceiver(Store $receiver, StoreCollection $senders = null): DeliveryScheduleCollection
    {
        $getSchedules = function () use ($receiver) {
            return ['result' => $this->repository->findByReceiver($receiver)];
        };

        try {
            /** @var DeliveryScheduleCollection $schedules */
            $schedules = (new BitrixCache())
                ->withId(__METHOD__ . $receiver->getXmlId())
                ->withTag('delivery_schedule')
                ->resultOf($getSchedules)['result'];
            if ($senders && !$senders->isEmpty()) {
                $schedules = $schedules->filterBySenders($senders);
                /** @var DeliverySchedule $item */
                foreach ($schedules as $item) {
                    $item->setReceiver($receiver);
                    if (isset($senders[$item->getSenderCode()])) {
                        $item->setSender($senders[$item->getSenderCode()]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get delivery schedules: %s', $e->getMessage()),
                ['receiver' => $receiver->getXmlId()]
            );

            $schedules = new DeliveryScheduleCollection();
        }

        return $schedules;
    }


    public function findByReceiverAndRegularity(Store $receiver, int $regularityId, StoreCollection $senders = null): DeliveryScheduleCollection
    {
        $getSchedules = function () use ($receiver, $regularityId) {
            return ['result' => $this->repository->findByReceiverAndRegularity($receiver, $regularityId)];
        };

        try {
            /** @var DeliveryScheduleCollection $schedules */
            $schedules = (new BitrixCache())
                ->withId(__METHOD__ . $receiver->getXmlId())
                ->withTag('delivery_schedule')
                ->resultOf($getSchedules)['result'];
            if ($senders && !$senders->isEmpty()) {
                $schedules = $schedules->filterBySenders($senders);
                /** @var DeliverySchedule $item */
                foreach ($schedules as $item) {
                    $item->setReceiver($receiver);
                    if (isset($senders[$item->getSenderCode()])) {
                        $item->setSender($senders[$item->getSenderCode()]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get delivery schedules: %s', $e->getMessage()),
                ['receiver' => $receiver->getXmlId()]
            );

            $schedules = new DeliveryScheduleCollection();
        }

        return $schedules;
    }

    /**
     * @param Store $sender
     * @param null|StoreCollection $receivers
     *
     * @return DeliveryScheduleCollection
     */
    public function findBySender(Store $sender, StoreCollection $receivers = null): DeliveryScheduleCollection
    {
        $getSchedules = function () use ($sender) {
            return ['result' => $this->repository->findBySender($sender)];
        };

        try {
            /** @var DeliveryScheduleCollection $schedules */
            $schedules = (new BitrixCache())
                ->withId(__METHOD__ . $sender->getXmlId())
                ->withTag('delivery_schedule')
                ->resultOf($getSchedules)['result'];
            if ($receivers && !$receivers->isEmpty()) {
                $schedules = $schedules->filterByReceivers($receivers);
                /** @var DeliverySchedule $item */
                foreach ($schedules as $item) {
                    $item->setSender($sender);
                    if (isset($receivers[$item->getReceiverCode()])) {
                        $item->setReceiver($receivers[$item->getReceiverCode()]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get delivery schedules: %s', $e->getMessage()),
                ['sender' => $sender->getXmlId()]
            );

            $schedules = new DeliveryScheduleCollection();
        }

        return $schedules;
    }

    public function findBySenderAndRegularity(Store $sender, int $regularityId, StoreCollection $receivers = null): DeliveryScheduleCollection
    {
        $getSchedules = function () use ($sender, $regularityId) {
            return ['result' => $this->repository->findBySenderAndRegularity($sender, $regularityId)];
        };

        try {
            /** @var DeliveryScheduleCollection $schedules */
//            $schedules = (new BitrixCache())
//                ->withId(__METHOD__ . $sender->getXmlId() . $regularityId)
//                ->withTag('delivery_schedule')
//                ->resultOf($getSchedules)['result'];

            $cacheKey = explode('::', __METHOD__ )[1] . $sender->getXmlId() . $regularityId . 'ddd';
            $cache = new FilesystemCache('', 3600, getenv('CACHE_DIR') ?? null);

            if ($cache->has($cacheKey)) {
                $schedules = $cache->get($cacheKey);
            } else {
                $cache->deleteItem($cacheKey);
                $schedules = $this->repository->findBySenderAndRegularity($sender, $regularityId);
                $cache->set($cacheKey, $schedules);
            }

            if ($receivers && !$receivers->isEmpty()) {
                $schedules = $schedules->filterByReceivers($receivers);
                /** @var DeliverySchedule $item */
                foreach ($schedules as $item) {
                    $item->setSender($sender);
                    if (isset($receivers[$item->getReceiverCode()])) {
                        $item->setReceiver($receivers[$item->getReceiverCode()]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to get delivery schedules: %s', $e->getMessage()),
                ['sender' => $sender->getXmlId()]
            );

            $schedules = new DeliveryScheduleCollection();
        }

        return $schedules;
    }

    /**
     * @param StoreCollection $stores
     *
     * @return DeliveryScheduleCollection
     */
    public function findByReceivers(StoreCollection $stores): DeliveryScheduleCollection
    {
        $results = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            $results[] = $this->findByReceiver($store)->toArray();
        }

        if (!empty($results)) {
            $results = array_merge(...$results);
        }

        return new DeliveryScheduleCollection($results);
    }

    /**
     * @param StoreCollection $stores
     *
     * @return DeliveryScheduleCollection
     */
    public function findBySenders(StoreCollection $stores): DeliveryScheduleCollection
    {
        $results = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            $results[] = $this->findBySender($store)->toArray();
        }

        if (!empty($results)) {
            $results = array_merge(...$results);
        }

        return new DeliveryScheduleCollection($results);
    }

    /**
     * @param string $xmlId
     *
     * @throws NotFoundException
     * @return DeliverySchedule
     */
    public function findByXmlId(string $xmlId): DeliverySchedule
    {
        return $this->repository->findByXmlId($xmlId);
    }

    /**
     * @param int $typeId
     *
     * @return null|string
     */
    public function getTypeCodeById(int $typeId): ?string
    {
        return $this->getTypes()[$typeId];
    }

    /**
     * @param string $code
     *
     * @return int|null
     */
    public function getTypeIdByCode(string $code): ?int
    {
        return array_flip($this->getTypes())[$code];
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        $getTypes = function () {
            $result = [];

            if ($enums = (new \CUserFieldEnum())->GetList([], ['USER_FIELD_NAME' => static::TYPE_FIELD_CODE])) {
                while ($enum = $enums->Fetch()) {
                    $result[(int)$enum['ID']] = $enum['XML_ID'];
                }
            }

            return ['result' => $result];
        };

        $result = [];
        try {
            $result = $getTypes()['result'];
//            $cacheKey = 'delivery_schedule';
//            $cache = new FilesystemCache('', 3600);
//            if ($cache->has($cacheKey)) {
//                $result = $cache->get($cacheKey);
//            } else {
//                $result = $getTypes()['result'];
//                $cache->set($cacheKey, $result);
//            }
//            $result = (new BitrixCache())
//                ->withId(__METHOD__)
//                ->withTag('delivery_schedule')
//                ->resultOf($getTypes)['result'];
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get enum list: %s', $e->getMessage()));
        }

        return $result;
    }

    /**
     * Возвращает порядковые номера дней недели
     *
     * @param array $orderDays
     * @return array
     */
    public function getWeeknums(array $weekDays)
    {
        $days = [];
        foreach(array_keys($weekDays) as $weekday){
            $days[] = array_search(strtolower($weekday), $this->weekdays);
        }

        return $days;
    }

    /**
     * Возвращает дни для формирования заказа и соответствующие дни поставки
     *
     * @param DeliverySchedule $schedule
     * @param \DateTime|null $from
     * @return OrderDayCollection|null
     * @throws \Exception
     */
    public function getOrderAndSupplyDays(DeliverySchedule $schedule, \DateTime $from = null): ?OrderDayCollection
    {
        $result = new OrderDayCollection;

        $type = $schedule->getTypeCode();
        $orderDays = $schedule->getOrderDays();
        $supplyDays = $schedule->getSupplyDays();
        $regularity = $schedule->getRegular();
        if(!$regularity){
            throw new \Exception("Не указана регулярность расписания");
        }

        /** @var UserFieldEnumValue $reg */
        foreach ($this->getRegular() as $reg){
            if($reg->getId() == $regularity){
                $regularityCode = $reg->getXmlId();
                break;
            }
        }

        switch ($regularityCode){
            case 'Z2':
                $orderTime = $schedule->getSender()->getStoreOrderTimeI();
                break;
            default:
                $orderTime = $schedule->getSender()->getStoreOrderTime();
                break;
        }

        if(!$from){
            $from = new \DateTime();
        }

        foreach ($orderDays as $index => $orderDay){
            $supplyDay = $supplyDays[$index];
            $result->add(new OrderDay($orderDay, $supplyDay, $type, $from, $orderTime));
        }

        return !$result->isEmpty() ? $result : null;
    }

    /**
     * @return \FourPaws\AppBundle\Collection\UserFieldEnumCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getRegular()
    {
        if(null === $this->regular){
            /** @var UserFieldEnumService $userFieldEnumService */
            $userFieldEnumService = Application::getInstance()->getContainer()->get('userfield_enum.service');
            $userFieldId = UserFieldTable::query()->setSelect(['ID', 'XML_ID'])->setFilter(
                [
                    'FIELD_NAME' => 'UF_REGULARITY',
                    'ENTITY_ID' => 'HLBLOCK_' . HighloadHelper::getIdByName(HlblockCode::DELIVERY_SCHEDULE),
                ]
            )->exec()->fetch()['ID'];
            $this->regular = $userFieldEnumService->getEnumValueCollection($userFieldId);
        }
        return $this->regular;
    }
}
