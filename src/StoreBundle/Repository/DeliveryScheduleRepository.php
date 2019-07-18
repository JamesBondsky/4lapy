<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Enum\HlblockCode;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class DeliveryScheduleRepository
 *
 * @package FourPaws\StoreBundle\Repository
 */
class DeliveryScheduleRepository extends BaseRepository
{
    /**
     * @var DataManager
     */
    protected $dataManager;
    protected $hlEntityFields;
    protected $hlBlockData;

    /**
     * DeliveryScheduleRepository constructor.
     *
     * @param ArrayTransformerInterface $arrayTransformer
     * @param ValidatorInterface $validator
     *
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.deliveryschedule.tpz');

        parent::__construct($arrayTransformer, $validator);
    }

    /**
     * @param string $xmlId
     * @throws NotFoundException
     * @return DeliverySchedule
     */
    public function findByXmlId(string $xmlId): DeliverySchedule
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        $result = $this->findBy(['UF_TPZ_XML_ID' => $xmlId], [], 1)->first();
        if (!$result) {
            throw new NotFoundException(sprintf('Schedule with xmlId %s not found', $xmlId));
        }
        return $result;
    }

    /**
     * @param Store $receiver
     * @param StoreCollection $senders
     * @return DeliveryScheduleCollection
     */
    public function findByReceiver(
        Store $receiver,
        StoreCollection $senders = null
    ): DeliveryScheduleCollection {
        $filter = ['=UF_TPZ_RECEIVER' => $receiver->getXmlId()];
        if ($senders && !$senders->isEmpty()) {
            $filter['=UF_TPZ_RECEIVER'] = [];
            /** @var Store $sender */
            foreach ($senders as $sender) {
                $filter['=UF_TPZ_SENDER'][] = $sender->getXmlId();
            }
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findBy($filter);
    }

    /**
     * @param Store $receiver
     * @param int $regularityId
     * @param StoreCollection|null $senders
     * @return DeliveryScheduleCollection
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function findByReceiverAndRegularity(
        Store $receiver,
        int $regularityId,
        StoreCollection $senders = null
    ): DeliveryScheduleCollection {
        $filter = ['=UF_TPZ_RECEIVER' => $receiver->getXmlId(), '=UF_REGULARITY' => $regularityId];
        if ($senders && !$senders->isEmpty()) {
            $filter['=UF_TPZ_RECEIVER'] = [];
            /** @var Store $sender */
            foreach ($senders as $sender) {
                $filter['=UF_TPZ_SENDER'][] = $sender->getXmlId();
            }
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findBy($filter);
    }

    /**
     * @param Store $sender
     * @param StoreCollection $receivers
     * @return DeliveryScheduleCollection
     */
    public function findBySender(
        Store $sender,
        StoreCollection $receivers = null
    ): DeliveryScheduleCollection {
        $filter = ['=UF_TPZ_SENDER' => $sender->getXmlId()];
        if ($receivers && !$receivers->isEmpty()) {
            $filter['=UF_TPZ_RECEIVER'] = [];
            /** @var Store $sender */
            foreach ($receivers as $receiver) {
                $filter['=UF_TPZ_RECEIVER'][] = $receiver->getXmlId();
            }
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findBy($filter);
    }

    /**
     * @param Store $sender
     * @param StoreCollection $receivers
     * @return DeliveryScheduleCollection
     */
    public function findBySenderAndRegularity(
        Store $sender,
        int $regularityId,
        StoreCollection $receivers = null
    ): DeliveryScheduleCollection {
        $filter = ['=UF_TPZ_SENDER' => $sender->getXmlId(), '=UF_REGULARITY' => $regularityId];
        if ($receivers && !$receivers->isEmpty()) {
            $filter['=UF_TPZ_RECEIVER'] = [];
            /** @var Store $sender */
            foreach ($receivers as $receiver) {
                $filter['=UF_TPZ_RECEIVER'][] = $receiver->getXmlId();
            }
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findBy($filter);
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getHlBlockEntityFields(): array
    {
        if (!$this->hlEntityFields) {
            $this->hlEntityFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('HLBLOCK_'.$this->getHlBlockId());
        }
        return $this->hlEntityFields;
    }

    /**
     * @return int
     * @throws ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    public function getHlBlockId(): int
    {
        $highloadHelper = new HighloadHelper();
        return $highloadHelper->getIdByName(HlblockCode::DELIVERY_SCHEDULE);
    }

    /**
     * @return string
     */
    protected function getDataClass(): string
    {
        return \get_class($this->dataManager);
    }

    /**
     * @return string
     */
    protected function getCollectionClass(): string
    {
        return DeliveryScheduleCollection::class;
    }

    /**
     * @return string
     */
    protected function getEntityClass(): string
    {
        return DeliverySchedule::class;
    }

    /**
     * @return array
     */
    protected function getDefaultOrder(): array
    {
        return ['ID' => 'ASC'];
    }

    /**
     * @return array
     */
    protected function getDefaultFilter(): array
    {
        return [
            '!UF_TPZ_TYPE' => false,
            'SENDER_STORE.ACTIVE' => 'Y',
            'RECEIVER_STORE.ACTIVE' => 'Y'
        ];
    }

    /**
     * @param Query $query
     *
     * @throws ArgumentException
     * @throws SystemException
     * @return Query
     */
    protected function modifyQuery(Query $query): Query
    {
        $query->registerRuntimeField(
            new ReferenceField(
                'SENDER_STORE',
                StoreTable::class,
                ['=this.UF_TPZ_SENDER' => 'ref.XML_ID'],
                ['join_type' => 'INNER'])
        )->registerRuntimeField(
            new ReferenceField(
                'RECEIVER_STORE',
                StoreTable::class,
                ['=this.UF_TPZ_RECEIVER' => 'ref.XML_ID'],
                ['join_type' => 'INNER'])
        );

        return parent::modifyQuery($query);
    }
}
