<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\StoreBundle\Collection\ScheduleResultCollection;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\ConstraintDefinitionException;
use FourPaws\StoreBundle\Exception\InvalidIdentifierException;
use FourPaws\StoreBundle\Exception\NotFoundException;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ScheduleResultRepository extends BaseRepository
{
    /**
     * @var DataManager
     */
    protected $dataManager;

    /**
     * ScheduleResultRepository constructor.
     *
     * @param ArrayTransformerInterface $arrayTransformer
     * @param ValidatorInterface $validator
     *
     * @throws ApplicationCreateException
     */
    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.deliveryscheduleresult');

        parent::__construct($arrayTransformer, $validator);
    }


    protected function getDefaultFilter(): array
    {
        return [];
    }

    protected function getDefaultOrder(): array
    {
        return [];
    }

    protected function getCollectionClass(): string
    {
        return ScheduleResultCollection::class;
    }

    protected function getEntityClass(): string
    {
        return ScheduleResult::class;
    }

    protected function getDataClass(): string
    {
        return \get_class($this->dataManager);
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @return ScheduleResult
     */
    public function find(int $id): ScheduleResult
    {
        $result =parent::find($id);
        if (!$result instanceof ScheduleResult) {
            throw new NotFoundException(sprintf('ScheduleResult with id %s not found', $id));
        }

        return $result;
    }

    /**
     * @param Store $sender
     *
     * @return ScheduleResultCollection
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function findBySender(Store $sender): ScheduleResultCollection
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findBy(['UF_SENDER' => $sender->getXmlId()]);
    }

    /**
     * @param Store $receiver
     *
     * @return ScheduleResultCollection
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function findByReceiver(Store $receiver): ScheduleResultCollection
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findBy(['UF_RECEIVER' => $receiver->getXmlId()]);
    }

    /**
     * @param Store $sender
     * @param Store $receiver
     *
     * @return ScheduleResultCollection
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function findBySenderAndReceiver(Store $sender, Store $receiver): ScheduleResultCollection
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findBy([
            'UF_SENDER' => $sender->getXmlId(),
            'UF_RECEIVER' => $receiver->getXmlId()
        ]);
    }
}
