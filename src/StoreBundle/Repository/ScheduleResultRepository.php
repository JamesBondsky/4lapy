<?php

namespace FourPaws\StoreBundle\Repository;

use FourPaws\AppBundle\Exception\RuntimeException;
use FourPaws\BitrixOrmBundle\Orm\D7Repository;
use FourPaws\StoreBundle\Collection\ScheduleResultCollection;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Exception\NotFoundException;
use WebArch\BitrixCache\BitrixCache;

class ScheduleResultRepository extends D7Repository
{
    /**
     * @var ScheduleResultCollection[]
     */
    protected $bySender = [];

    /**
     * @var ScheduleResultCollection[]
     */
    protected $byReceiver = [];

    /**
     * @param int $id
     *
     * @return ScheduleResult
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function find($id): ScheduleResult
    {
        $result =parent::find($id);
        if (!$result instanceof ScheduleResult) {
            throw new NotFoundException(sprintf('ScheduleResult with id %s not found', $id));
        }

        return $result;
    }

    /**
     * @param string $senderXmlId
     *
     * @return ScheduleResultCollection
     * @throws \Exception
     */
    public function findBySender(string $senderXmlId): ScheduleResultCollection
    {
        if (null === $this->bySender[$senderXmlId]) {
            $getResults = function () use ($senderXmlId) {
                $scheduleResults = $this->findBy(['UF_SENDER' => $senderXmlId])->toArray();

                return ['result' => new ScheduleResultCollection($scheduleResults)];
            };

            $result = (new BitrixCache())->withTag('catalog:store:schedule:results')
                                         ->withId(__METHOD__ . $senderXmlId)
                                         ->resultOf($getResults)['result'];

            $this->bySender[$senderXmlId] = $result;
        }

        return $this->bySender[$senderXmlId];
    }

    /**
     * @param string $receiverXmlId
     *
     * @return ScheduleResultCollection
     * @throws \Exception
     */
    public function findByReceiver(string $receiverXmlId): ScheduleResultCollection
    {
        if (null === $this->byReceiver[$receiverXmlId]) {
            $getResults = function () use ($receiverXmlId) {
                $scheduleResults = $this->findBy(['UF_RECEIVER' => $receiverXmlId])->toArray();

                return ['result' => new ScheduleResultCollection($scheduleResults)];
            };

            $result = (new BitrixCache())->withTag('catalog:store:schedule:results')
                                         ->withId(__METHOD__ . $receiverXmlId)
                                         ->resultOf($getResults)['result'];

            $this->byReceiver[$receiverXmlId] = $result;
        }

        return $this->byReceiver[$receiverXmlId];
    }
}
