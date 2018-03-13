<?php

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Main\Entity\DataManager;
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
     * @param array $filter
     * @return DeliveryScheduleCollection
     * @throws NotFoundException
     */
    public function findByReceiver(
        Store $receiver,
        StoreCollection $senders = null,
        array $filter = []
    ): DeliveryScheduleCollection {
        $filter = array_merge(['UF_RECEIVER' => $receiver->getXmlId()], $filter);

        if ($senders && !$senders->isEmpty()) {
            $filter['UF_SENDER'] = [];
            /** @var Store $sender */
            foreach ($senders as $sender) {
                $filter['UF_SENDER'][] = $sender->getXmlId();
            }
        }

        return $this->findBy($filter);
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return DeliveryScheduleCollection
     * @throws NotFoundException
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        int $limit = null,
        int $offset = null
    ): DeliveryScheduleCollection {
        if (empty($orderBy)) {
            $orderBy = $this->getDefaultOrder();
        }

        $criteria = array_merge($this->getDefaultFilter(), $criteria);

        $entities = $this->table::query()
            ->setSelect(['*', 'UF_*'])
            ->setFilter($criteria)
            ->setOrder($orderBy)
            ->setLimit($limit)
            ->setOffset($offset)
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
    }

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
        return ['UF_ACTIVE' => true];
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
