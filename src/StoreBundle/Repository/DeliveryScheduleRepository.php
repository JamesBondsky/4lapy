<?php

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeliveryScheduleRepository extends BaseRepository
{
    /**
     * @var DataManager
     */
    protected $dataManager;

    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.deliveryschedule');
        parent::__construct($arrayTransformer, $validator);
    }

    protected function getDataClass(): string
    {
        return get_class($this->dataManager);
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
        return ['UF_ACTIVE' => true];
    }
}
