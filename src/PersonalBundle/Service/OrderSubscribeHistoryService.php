<?php

namespace FourPaws\PersonalBundle\Service;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\RuntimeException;

/**
 * Class OrderSubscribeHistoryService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class OrderSubscribeHistoryService
{
    const HL_SERVICE_NAME = 'bx.hlblock.ordersubscribehistory';
    /** @var OrderSubscribeService */
    private $orderSubscribeService;
    /** @var DataManager */
    private $dataManager;

    /**
     * OrderSubscribeHistoryService constructor.
     * @throws ApplicationCreateException
     * @throws RuntimeException
     */
    public function __construct()
    {
        $this->dataManager = Application::getHlBlockDataManager(static::HL_SERVICE_NAME);
        /**
         * Здесь делается дополнительная проверка на Bitrix\Highloadblock\DataManager,
         * т.к. в методе допускается и Bitrix\Main\DataManager
         **/
        if (!$this->dataManager || !($this->dataManager instanceof DataManager)) {
            throw new RuntimeException(
                sprintf(
                    'Сервис %s не является %s',
                    static::HL_SERVICE_NAME,
                    DataManager::class
                )
            );
        }
    }

    /**
     * @return OrderSubscribeService
     * @throws ApplicationCreateException
     */
    public function getOrderSubscribeService(): OrderSubscribeService
    {
        if (!isset($this->orderService)) {
            $this->orderSubscribeService = Application::getInstance()->getContainer()->get('order_subscribe.service');
        }

        return $this->orderSubscribeService;
    }

    /**
     * @param int $originOrderId
     * @param \DateTime $deliveryDate
     * @return bool
     * @throws ArgumentException
     */
    public function wasOrderCreated(int $originOrderId, \DateTime $deliveryDate): bool
    {
        $items = $this->findBy(
            [
                'select' => [
                    'ID'
                ],
                'filter' => [
                    '=UF_ORIGIN_ORDER_ID' => (int)$originOrderId,
                    '=UF_DELIVERY_DATE' => new Date($deliveryDate->format('d.m.Y')),
                ],
                'limit' => 1
            ]
        );

        return $items->getSelectedRowsCount() > 0;
    }

    /**
     * @return AddResult
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function add(): AddResult
    {
        $args = func_get_args();

        /** @var OrderSubscribe $orderSubscribe */
        $orderSubscribe = null;
        $newOrderId = 0;
        $deliveryDate = null;
        $fields = [];
        $argsCnt = count($args);
        if ($argsCnt === 3 && ($args[0] instanceof OrderSubscribe) && ((int)$args[1] > 0) && ($args[2] instanceof \DateTime)) {
            $orderSubscribe = $args[0];
            $newOrderId = (int)$args[1];
            $deliveryDate = $args[2];
        } elseif ($argsCnt === 2 && ($args[0] instanceof OrderSubscribe) && (is_int($args[1]))) {
            $orderSubscribe = $args[0];
            $newOrderId = (int)$args[1];
            $deliveryDate = $orderSubscribe->getNextDeliveryDate();
        } elseif ($argsCnt === 1 && is_array($args[0])) {
            $fields = $args[0];
        } else {
            throw new InvalidArgumentException('Wrong arguments');
        }

        if ($orderSubscribe) {
            $fields = [
                'UF_ORIGIN_ORDER_ID' => $orderSubscribe->getOrderId(),
                'UF_NEW_ORDER_ID' => $newOrderId,
                'UF_DATE_CREATE' => (new DateTime()),
                'UF_DELIVERY_DATE' => (new Date($deliveryDate->format('d.m.Y'))),
                'UF_SUBS_DATA' => $this->getSubsDataByOrderSubscribe($orderSubscribe),
            ];
        }

        $addResult = $this->dataManager::add($fields);

        return $addResult;
    }

    /**
     * @param OrderSubscribe $orderSubscribe
     * @return string
     */
    public function getSubsDataByOrderSubscribe(OrderSubscribe $orderSubscribe): string
    {
        return serialize($orderSubscribe->getAllFields());
    }

    /**
     * @param array $params
     * @return \Bitrix\Main\DB\Result
     * @throws ArgumentException
     */
    protected function findBy(array $params): \Bitrix\Main\DB\Result
    {
        $result = $this->dataManager::getList($params);

        return $result;
    }
}
