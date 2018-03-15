<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Repository\OrderStorage;

use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\OrderStorageSaveException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Service\OrderStorageService;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;

class DatabaseStorageRepository extends StorageBaseRepository
{

    /**
     * @param OrderStorage $storage
     * @param string $step
     *
     * @return bool
     * @throws OrderStorageSaveException
     * @throws OrderStorageValidationException
     */
    public function save(OrderStorage $storage, string $step = OrderStorageService::AUTH_STEP): bool
    {
        $validationResult = $this->validate($storage, $step);
        if ($validationResult->count() > 0) {
            throw new OrderStorageValidationException($validationResult, 'Wrong entity passed to create');
        }

        $data = $this->arrayTransformer->toArray(
            $storage,
            SerializationContext::create()->setGroups(
                ['update']
            )
        );

        $result = Table::update(
            $storage->getFuserId(),
            $this->prepareData($data)
        );

        if (!$result->isSuccess()) {
            throw new OrderStorageSaveException(
                sprintf('Ошибка при сохранении данных по заказу: %s', implode(', ', $result->getErrorMessages()))
            );
        }

        return true;
    }

    /**
     * @param OrderStorage $storage
     *
     * @return bool
     */
    public function clear(OrderStorage $storage): bool
    {
        $result = Table::delete($storage->getFuserId());
        if (!$result->isSuccess()) {
            return false;
        }

        return true;
    }

    /**
     * @param int $fuserId
     * @return OrderStorage
     * @throws OrderStorageSaveException
     */
    public function findByFuser(int $fuserId): OrderStorage
    {
        if ($data = Table::getByPrimary($fuserId)->fetch()) {
            $data = array_merge($data, $data['UF_DATA']);
            unset($data['UF_DATA']);
            $data = $this->setInitialValues($data);
        } else {
            $data = $this->setInitialValues([]);
            $this->create($data);
        }

        return $this->arrayTransformer->fromArray(
            $data,
            OrderStorage::class,
            DeserializationContext::create()->setGroups(['read'])
        );
    }

    /**
     * Чтобы не делать много столбцов в таблице, было решено большинство полей
     * хранить в одном serialized-поле, потому здесь и появился этот метод
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareData(array $data): array
    {
        $result = [
            'UF_FUSER_ID' => $data['UF_FUSER_ID'],
            'UF_USER_ID'  => $data['UF_USER_ID'],
        ];
        unset($data['UF_FUSER_ID']);
        unset($data['UF_USER_ID']);
        $result['UF_DATA'] = $data;

        return $result;
    }

    /**
     * @param array $data
     *
     * @throws OrderStorageSaveException
     * @return bool
     */
    protected function create(array $data): bool
    {
        $result = Table::add($this->prepareData($data));
        if (!$result->isSuccess()) {
            throw new OrderStorageSaveException(
                'Failed to save order storage',
                ['messages' => $result->getErrorMessages(), 'fuserId' => $data['UF_FUSER_ID']]
            );
        }

        return true;
    }
}
