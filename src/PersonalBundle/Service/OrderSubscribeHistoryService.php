<?php

namespace FourPaws\PersonalBundle\Service;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Result;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\ReferenceField;
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
            $this->orderSubscribeService = Application::getInstance()->getContainer()->get(
                'order_subscribe.service'
            );
        }

        return $this->orderSubscribeService;
    }

    /**
     * @param int $originOrderId
     * @param \DateTimeInterface $deliveryDate
     * @return \Bitrix\Main\DB\Result
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getCreatedOrders(int $originOrderId, \DateTimeInterface $deliveryDate): \Bitrix\Main\DB\Result
    {
        $items = $this->findBy(
            [
                'select' => [
                    'ID',
                    'UF_NEW_ORDER_ID'
                ],
                'filter' => [
                    '=ORDER.CANCELED' => 'N',
                    '=UF_ORIGIN_ORDER_ID' => (int)$originOrderId,
                    '=UF_DELIVERY_DATE' => new Date($deliveryDate->format('d.m.Y')),
                ],
                'runtime' => [
                    new ReferenceField(
                        'ORDER',
                        \Bitrix\Sale\Internals\OrderTable::getEntity(),
                        [
                            '=this.UF_NEW_ORDER_ID' => 'ref.ID',
                        ],
                        [
                            //'join_type' => 'inner'
                        ]
                    ),
                ],
                //'limit' => 1
            ]
        );

        return $items;
    }

    /**
     * @param int $originOrderId
     * @param \DateTimeInterface $deliveryDate
     * @return int
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getCreatedOrderId(int $originOrderId, \DateTimeInterface $deliveryDate): int
    {
        $orderId = 0;
        $item = $this->getCreatedOrders($originOrderId, $deliveryDate)->fetch();
        if ($item) {
            $orderId = (int)$item['UF_NEW_ORDER_ID'];
        }

        return $orderId;
    }

    /**
     * @param int $originOrderId
     * @param \DateTimeInterface $deliveryDate
     * @return bool
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function wasOrderCreated(int $originOrderId, \DateTimeInterface $deliveryDate): bool
    {
        $items = $this->getCreatedOrders($originOrderId, $deliveryDate);

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
        if ($argsCnt === 3 && ($args[0] instanceof OrderSubscribe) && ((int)$args[1] > 0) && ($args[2] instanceof \DateTimeInterface)) {
            $orderSubscribe = $args[0];
            $newOrderId = (int)$args[1];
            $deliveryDate = $args[2];
        } elseif ($argsCnt === 2 && ($args[0] instanceof OrderSubscribe) && (is_int($args[1]))) {
            $orderSubscribe = $args[0];
            $newOrderId = (int)$args[1];
            $deliveryDate = $orderSubscribe->getNextDate();
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
        $fields = $orderSubscribe->getAllFields();
        if ($fields['UF_DATE_CREATE'] && $fields['UF_DATE_CREATE'] instanceof Date) {
            $fields['UF_DATE_CREATE'] = $fields['UF_DATE_CREATE']->toString();
        }
        if ($fields['UF_DATE_EDIT'] && $fields['UF_DATE_EDIT'] instanceof Date) {
            $fields['UF_DATE_EDIT'] = $fields['UF_DATE_EDIT']->toString();
        }
        if ($fields['UF_DATE_START'] && $fields['UF_DATE_START'] instanceof Date) {
            $fields['UF_DATE_START'] = $fields['UF_DATE_START']->toString();
        }
        if ($fields['UF_LAST_CHECK'] && $fields['UF_LAST_CHECK'] instanceof Date) {
            $fields['UF_LAST_CHECK'] = $fields['UF_LAST_CHECK']->toString();
        }

        return serialize($fields);
    }

    /**
     * Возвращает id последней копии заказа, созданного по подписке
     *
     * @param int $originOrderId
     * @return int
     * @throws ArgumentException
     * @throws SystemException
     */
    public function getLastCreatedOrderId(OrderSubscribe $orderSubscribe): int
    {
        $lastCreatedOrderId = 0;
        $originOrderId = $orderSubscribe->getOrderId();
        $params = [
            'order' => [
                'UF_NEW_ORDER_ID' => 'desc',
            ],
            'filter' => [
                'UF_ORIGIN_ORDER_ID' => $originOrderId,
                '!=ORDER.ID' => false,
            ],
            'runtime' => [
                new ReferenceField(
                    'ORDER',
                    \Bitrix\Sale\Internals\OrderTable::getEntity(),
                    [
                        '=this.UF_NEW_ORDER_ID' => 'ref.ID',
                    ],
                    [
                        //'join_type' => 'inner'
                    ]
                ),
            ],
            //'limit' => 1,
            'select' => [
                'ID', 'UF_NEW_ORDER_ID', 'UF_SUBS_DATA'
            ]
        ];
        $dbres = $this->findBy($params);
        while ($item = $dbres->fetch()) {
            $subData = unserialize($item['UF_SUBS_DATA']);
            if($subData['ID'] != $orderSubscribe->getId()){
                continue;
            }
            $lastCreatedOrderId = $item['UF_NEW_ORDER_ID'];
            break;
        }

        return $lastCreatedOrderId;
    }

    /**
     * Возвращает id созданных, но ещё не доставленных заказов по подписке
     *
     * @param OrderSubscribe $orderSubscribe
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     */
    public function getNotDeliveredOrderIds(OrderSubscribe $orderSubscribe)
    {
        $orderIds = [];
        $params = [
            'order' => [
                'UF_NEW_ORDER_ID' => 'desc',
            ],
            'filter' => [
                'ORDER.CANCELED' => 'N',
                'UF_ORIGIN_ORDER_ID' => $orderSubscribe->getOrderId(),
                '!=ORDER.ID' => false,
                '>=UF_DELIVERY_DATE' => new Date(),
            ],
            'runtime' => [
                new ReferenceField(
                    'ORDER',
                    \Bitrix\Sale\Internals\OrderTable::getEntity(),
                    [
                        '=this.UF_NEW_ORDER_ID' => 'ref.ID',
                    ],
                    [
                        //'join_type' => 'inner'
                    ]
                ),
            ],
            'select' => [
                'ID', 'UF_NEW_ORDER_ID', 'UF_SUBS_DATA'
            ]
        ];
        $dbres = $this->findBy($params);

        $orders = $this->fetchAllBySubscribeId($dbres, $orderSubscribe->getId());
        foreach($orders as $order){
            $orderIds[] = $order['UF_NEW_ORDER_ID'];
        }
        return $orderIds;
    }

    /**
     * @param array $params
     * @return \Bitrix\Main\DB\Result
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function findBy(array $params): \Bitrix\Main\DB\Result
    {
        $result = $this->dataManager::getList($params);
        return $result;
    }

    /**
     * @param OrderSubscribe $orderSubscribe
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getNearestDelivery(OrderSubscribe $orderSubscribe)
    {
        $dbres = $this->findBy(
            [
                'select' => [
                    'ID',
                    'UF_DELIVERY_DATE',
                    'UF_SUBS_DATA'
                ],
                'filter' => [
                    'ORDER.CANCELED' => 'N',
                    '=UF_ORIGIN_ORDER_ID' => (int)$orderSubscribe->getOrderId(),
                    '>=UF_DELIVERY_DATE' => new Date(),
                ],
                'runtime' => [
                    new ReferenceField(
                        'ORDER',
                        \Bitrix\Sale\Internals\OrderTable::getEntity(),
                        [
                            '=this.UF_NEW_ORDER_ID' => 'ref.ID',
                        ],
                        [
                            //'join_type' => 'inner'
                        ]
                    ),
                ],
                'order' => [
                    'UF_NEW_ORDER_ID' => 'asc',
                ],
            ]
        );

        $orders = $this->fetchAllBySubscribeId($dbres, $orderSubscribe->getId());
        return $orders[0] ? $orders[0]['UF_DELIVERY_DATE'] : $orderSubscribe->getNextDate();
    }

    public function getLastOrderDeliveryDate(OrderSubscribe $orderSubscribe)
    {
        $dbres = $this->findBy(
            [
                'select' => [
                    'ID',
                    'UF_DELIVERY_DATE',
                ],
                'filter' => [
                    '=UF_ORIGIN_ORDER_ID' => (int)$orderSubscribe->getOrderId()
                ],
                'order' => [
                    'ID' => 'DESC',
                ],
            ]
        );

        $orders = $this->fetchAllBySubscribeId($dbres, $orderSubscribe->getId());
        return $orders[0] ? $orders[0]['UF_DELIVERY_DATE'] : $orderSubscribe->getNextDate();
    }

    // [костыль] здесь сравнивается id подписки,
    // т.к. из ожного заказа можно сформировать несколько подписок
    protected function fetchAllBySubscribeId(Result $result, int $orderSubscribeId)
    {
        $results = [];
        while ($row = $result->fetch()) {
            $subData = unserialize($row['UF_SUBS_DATA']);
            if($subData['ID'] != $orderSubscribeId){
                continue;
            }
            $results[] = $row;
        }
        return $results;
    }
}
