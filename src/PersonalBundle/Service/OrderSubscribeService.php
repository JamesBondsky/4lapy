<?php

namespace FourPaws\PersonalBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Repository\OrderSubscribeRepository;
use FourPaws\SaleBundle\Helper\OrderCopy;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class OrderSubscribeService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class OrderSubscribeService
{
    /** @var OrderSubscribeRepository $orderSubscribeRepository */
    private $orderSubscribeRepository;
    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;
    /** @var OrderService $orderService */
    private $orderService;
    /** @var DeliveryService $deliveryService */
    private $deliveryService;
    /** @var array $miscData */
    private $miscData = [];

    /** @var array Исключаемые свойства корзины */
    private $basketItemExcludeProps = [
        /** @todo: Уточнить какие свойства позиции корзины следует исключать */
    ];
    /** @var array Исключаемые свойства заказа */
    private $orderExcludeProps = [
        'IS_EXPORTED', 'DELIVERY_DATE', 'DELIVERY_INTERVAL',
        /** @todo: Уточнить насчет этих свойств */
        'FROM_APP',
    ];

    /**
     * OrderSubscribeService constructor.
     *
     * @param OrderSubscribeRepository $orderSubscribeRepository
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(OrderSubscribeRepository $orderSubscribeRepository)
    {
        $this->orderSubscribeRepository = $orderSubscribeRepository;
    }

    /**
     * @return OrderService
     * @throws ApplicationCreateException
     */
    public function getOrderService(): OrderService
    {
        if (!isset($this->orderService)) {
            $this->orderService = Application::getInstance()->getContainer()->get('order.service');
        }

        return $this->orderService;
    }

    /**
     * @return CurrentUserProviderInterface
     * @throws ApplicationCreateException
     */
    public function getCurrentUserService(): CurrentUserProviderInterface
    {
        if (!isset($this->currentUser)) {
            $this->currentUser = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        }

        return $this->currentUser;
    }

    /**
     * @return DeliveryService
     * @throws ApplicationCreateException
     */
    public function getDeliveryService(): DeliveryService
    {
        if (!isset($this->deliveryService)) {
            $this->deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        }

        return $this->deliveryService;
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getFrequencyEnum(): array
    {
        if (!isset($this->miscData['FREQUENCY_ENUM'])) {
            $this->miscData['FREQUENCY_ENUM'] = [];
            $hlBlockEntityFields = $this->orderSubscribeRepository->getHlBlockEntityFields();
            if (isset($hlBlockEntityFields['UF_FREQUENCY'])) {
                if ($hlBlockEntityFields['UF_FREQUENCY']['USER_TYPE_ID'] === 'enumeration') {
                    // результат выборки кешируется внутри метода
                    $enumItems = (new \CUserFieldEnum())->GetList(
                        [
                            'SORT' => 'ASC'
                        ],
                        [
                            'USER_FIELD_ID' => $hlBlockEntityFields['UF_FREQUENCY']['ID']
                        ]
                    );
                    while ($item = $enumItems->Fetch()) {
                        $this->miscData['FREQUENCY_ENUM'][$item['ID']] = $item;
                    }
                }
            }
        }

        return $this->miscData['FREQUENCY_ENUM'];
    }

    /**
     * @param int $enumId
     * @return string
     * @throws ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getFrequencyXmlId(int $enumId): string
    {
        $enum = $this->getFrequencyEnum();

        return isset($enum[$enumId]) ? $enum[$enumId]['XML_ID'] : '';
    }

    /**
     * @param int $enumId
     * @return string
     * @throws ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getFrequencyValue(int $enumId): string
    {
        $enum = $this->getFrequencyEnum();

        return isset($enum[$enumId]) ? $enum[$enumId]['VALUE'] : '';
    }

    /**
     * @param int|array $orderId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getSubscriptionsByOrder($orderId, $filterActive = true)
    {
        $params = [];
        if ($filterActive) {
            $params['=UF_ACTIVE'] = 1;
        }

        return $this->orderSubscribeRepository->findByOrder($orderId, $params);
    }

    /**
     * @param int|array $userId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getSubscriptionsByUser($userId, $filterActive = true)
    {
        $params = [];
        if ($filterActive) {
            $params['=UF_ACTIVE'] = 1;
        }

        return $this->orderSubscribeRepository->findByUser($userId, $params);
    }

    /**
     * @param int $orderId
     * @return Order|null
     * @throws \Exception
     */
    public function getOrderById(int $orderId)
    {
        return $this->getOrderService()->getOrderById($orderId);
    }

    /**
     * @param int $userId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getUserSubscribedOrders(int $userId, $filterActive = true): ArrayCollection
    {
        $params = [
            'filter' => [
                'USER_ID' => $userId,
                '!=ORDER_SUBSCRIBE.ID' => false,
            ],
            'runtime' => [
                new ReferenceField(
                    'ORDER_SUBSCRIBE',
                    $this->orderSubscribeRepository->getHlBlockEntityClass(),
                    [
                        '=this.ID' => 'ref.UF_ORDER_ID'
                    ]
                ),
            ]
        ];
        if ($filterActive) {
            $params['filter']['=ORDER_SUBSCRIBE.UF_ACTIVE'] = 1;
        }

        return $this->getOrderService()->getUserOrders($params);
    }

    /**
     * @param array $data
     * @return AddResult
     */
    public function add(array $data): AddResult
    {
        $addResult = $this->orderSubscribeRepository->createEx($data);

        return $addResult;
    }

    /**
     * @param array $data
     * @return UpdateResult
     */
    public function update(array $data): UpdateResult
    {
        $updateResult = $this->orderSubscribeRepository->updateEx($data);

        return $updateResult;
    }

    /**
     * @param int $id
     * @return DeleteResult
     */
    public function delete(int $id): DeleteResult
    {
        $deleteResult = $this->orderSubscribeRepository->deleteEx($id);

        return $deleteResult;
    }

    /**
     * @param int $copyOrderId
     * @param bool $checkActiveSubscribe
     * @return Result
     */
    public function copyOrder(int $copyOrderId, bool $checkActiveSubscribe = true)
    {
        $result = new Result();

        /** @var OrderSubscribe $orderSubscribe */
        $orderSubscribe = null;
        try {
            $subscriptionsCollect = $this->getSubscriptionsByOrder($copyOrderId, $checkActiveSubscribe);
            $orderSubscribe = $subscriptionsCollect->first();
            if (!$orderSubscribe) {
                $result->addError(
                    new Error('Подписка на заказ не найдена', 'orderSubscribeNotFound')
                );
            }
        } catch (\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'orderSubscribeException')
            );
        }

        if ($result->isSuccess()) {
            try {
                $orderCopyHelper = new OrderCopy($copyOrderId);
                $orderCopyHelper->appendBasketItemExcludeProps(
                    $this->basketItemExcludeProps
                );
                $orderCopyHelper->appendOrderExcludeProps(
                    $this->orderExcludeProps
                );

                $orderCopyHelper->doFullCopy();
                $orderCopyHelper->setPropValueByCode(
                    'DELIVERY_INTERVAL',
                    $orderSubscribe->getDeliveryTime()
                );
                $deliveryDate = $orderSubscribe->getNextDeliveryDate();
                /** @todo: Если дата не определилась, то нужно ли создавать такой заказ? */
                $orderCopyHelper->setPropValueByCode(
                    'DELIVERY_DATE',
                    $deliveryDate ? $deliveryDate->format('d.m.Y') : ''
                );
                $orderCopyHelper->save();
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'orderCopyException')
                );
            }
        }

        return $result;
    }
}
