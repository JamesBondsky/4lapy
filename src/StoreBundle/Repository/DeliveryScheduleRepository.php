<?php

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
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
        $this->dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.deliveryschedule');

        parent::__construct($arrayTransformer, $validator);
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
        return [];
    }
}
