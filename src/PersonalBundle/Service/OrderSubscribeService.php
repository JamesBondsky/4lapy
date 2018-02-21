<?php

namespace FourPaws\PersonalBundle\Service;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Repository\OrderSubscribeRepository;
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
    /** @var array $miscData */
    private $miscData = [];

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
        $this->currentUser = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        $this->orderService = Application::getInstance()->getContainer()->get('order.service');
    }

    /**
     * @return OrderService
     */
    public function getOrderService(): OrderService
    {
        return $this->orderService;
    }

    /**
     * @param int $orderId
     * @return Order|null
     * @throws \Exception
     */
    public function getOrderById(int $orderId)
    {
        return $this->orderService->getOrderById($orderId);
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
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

    public function edit($orderId): array
    {

    }

}
