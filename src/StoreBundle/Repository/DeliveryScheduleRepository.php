<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use JMS\Serializer\ArrayTransformerInterface;
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
     * @param string $xmlId
     * @throws NotFoundException
     * @return DeliverySchedule
     */
    public function findByXmlId(string $xmlId): DeliverySchedule
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        $result = $this->findBy(['UF_XML_ID' => $xmlId], [], 1)->first();
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
        $filter = ['=UF_RECEIVER' => $receiver->getXmlId()];
        if ($senders && !$senders->isEmpty()) {
            $filter['=UF_SENDER'] = [];
            /** @var Store $sender */
            foreach ($senders as $sender) {
                $filter['=UF_SENDER'][] = $sender->getXmlId();
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
        $filter = ['=UF_SENDER' => $sender->getXmlId()];
        if ($receivers && !$receivers->isEmpty()) {
            $filter['=UF_RECEIVER'] = [];
            /** @var Store $sender */
            foreach ($receivers as $receiver) {
                $filter['=UF_RECEIVER'][] = $receiver->getXmlId();
            }
        }
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findBy($filter);
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
        return DeliverySchedule::class;
    }

    protected function getDefaultOrder(): array
    {
        return ['ID' => 'ASC'];
    }

    protected function getDefaultFilter(): array
    {
        return [];
    }
}
