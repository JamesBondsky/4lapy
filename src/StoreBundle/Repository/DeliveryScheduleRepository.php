<?php

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\ReferenceField;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule\DeliveryScheduleByWeek;
use FourPaws\StoreBundle\Entity\DeliverySchedule\DeliveryScheduleManual;
use FourPaws\StoreBundle\Entity\DeliverySchedule\DeliveryScheduleWeekly;
use FourPaws\StoreBundle\Entity\DeliverySchedule\DeliveryScheduleBase;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeliveryScheduleRepository extends BaseRepository
{
    /**
     * @var DataManager
     */
    protected $dataManager;

    /**
     * DeliveryScheduleRepository constructor.
     * @param ArrayTransformerInterface $arrayTransformer
     * @param ValidatorInterface $validator
     * @throws ApplicationCreateException
     */
    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.deliveryschedule');
        parent::__construct($arrayTransformer, $validator);
    }

    /**
     * @param Store $receiver
     * @param StoreCollection $senders
     * @param int $maxTransitions
     * @return DeliveryScheduleCollection
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function findByReceiver(
        Store $receiver,
        StoreCollection $senders = null,
        int $maxTransitions
    ): DeliveryScheduleCollection {
        $criteria = ['=UF_RECEIVER' => $receiver->getXmlId()];

        $query = $this->table::query();

        $senderXmlIds = [];
        if ($senders && !$senders->isEmpty()) {
            $filter['=UF_SENDER'] = [];
            /** @var Store $sender */
            foreach ($senders as $sender) {
                $senderXmlIds[] = $sender->getXmlId();
            }
        }

        if ($maxTransitions === 0) {
            if (!empty($senderXmlIds)) {
                $filter['=UF_SENDER'] = $senderXmlIds;
            }
        } elseif ($maxTransitions === 1) {
            $reference = ['=this.UF_SENDER' => '=ref.UF_RECEIVER'];
            if ($senderXmlIds) {
                $reference['=ref.UF_RECEIVER'] = $senderXmlIds;
            }
            $query->registerRuntimeField(
                new ReferenceField(
                    'SENDER',
                    $this->getDataClass(),
                    $reference,
                    ['join_type' => 'INNER']
            ));
        } else {
            /* @todo обработка случаев, когда в маршруте более одного промежуточного склада (пока не требуется) */
            return new DeliveryScheduleCollection();
        }

        $criteria = array_merge($this->getDefaultFilter(), $criteria);

        $entities = $query
            ->setSelect(['*', 'UF_*'])
            ->setFilter($criteria)
            ->exec();

        $result = [];
        while ($entity = $entities->fetch()) {
            $result[$entity['ID']] = $this->fromArray($entity);
        }

        /**
         * todo change group name to constant
         */
        $collectionClass = $this->getCollectionClass();

        return new $collectionClass($result);
    }/** @noinspection MoreThanThreeArgumentsInspection */

    protected function getDataClass(): string
    {
        return \get_class($this->dataManager);
    }

    protected function getCollectionClass(): string
    {
        return DeliveryScheduleCollection::class;
    }

    protected function getEntityClass(): string
    {
        return DeliveryScheduleBase::class;
    }

    protected function getDefaultOrder(): array
    {
        return ['ID' => 'ASC'];
    }

    protected function getDefaultFilter(): array
    {
        return [];
    }

    /**
     * @param array $fields
     * @return DeliveryScheduleBase
     * @throws NotFoundException
     */
    protected function fromArray(array $fields): DeliveryScheduleBase
    {
        $class = null;
        switch ($fields['UF_TYPE']) {
            case DeliveryScheduleBase::TYPE_MANUAL:
                $class = DeliveryScheduleManual::class;
                break;
            case DeliveryScheduleBase::TYPE_WEEKLY:
                $class = DeliveryScheduleWeekly::class;
                break;
            case DeliveryScheduleBase::TYPE_BY_WEEK:
                $class = DeliveryScheduleByWeek::class;
                break;
        }

        if (null === $class) {
            throw new NotFoundException(sprintf('Delivery schedule type %s is not allowed', $fields['UF_TYPE']));
        }

        return $this->arrayTransformer->fromArray(
            $fields,
            $class,
            DeserializationContext::create()->setGroups(['read'])
        );
    }
}
