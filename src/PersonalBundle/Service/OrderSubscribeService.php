<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 25.03.2019
 * Time: 17:25
 */

namespace FourPaws\PersonalBundle\Service;


use Bitrix\Main\Security\SecurityException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserFieldTable;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem;
use FourPaws\PersonalBundle\Repository\OrderSubscribeItemRepository;
use FourPaws\PersonalBundle\Repository\OrderSubscribeRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use http\Exception\InvalidArgumentException;
use mysql_xdevapi\Exception;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class OrderSubscribeService
{
    /** @var OrderSubscribeRepository $orderSubscribeRepository */
    private $orderSubscribeRepository;

    /** @var OrderSubscribeItemRepository $orderSubscribeRepository */
    private $orderSubscribeItemRepository;

    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;

    /** @var LocationService $locationService */
    private $locationService;

    /**
     * Интервалы доставки
     * @var array $frequencies
     */
    private $frequencies;

    /**
     * OrderSubscribeService constructor.
     *
     * @param OrderSubscribeRepository $orderSubscribeRepository
     * @param CurrentUserProviderInterface $currentUserProvider
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(
        OrderSubscribeRepository $orderSubscribeRepository,
        OrderSubscribeItemRepository $orderSubscribeItemRepository,
        CurrentUserProviderInterface $currentUserProvider,
        LocationService $locationService
    )
    {
        $this->orderSubscribeRepository = $orderSubscribeRepository;
        $this->orderSubscribeItemRepository = $orderSubscribeItemRepository;
        $this->currentUser = $currentUserProvider;
        $this->locationService = $locationService;
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     * @return bool
     */
    public function add(array $data): bool
    {
        if (empty($data['ITEMS'])) {
            throw new InvalidArgumentException('Для подписки на доставку необходимо передать список товаров');
        }
        if (empty($data['UF_USER_ID'])) {
            $data['UF_USER_ID'] = $this->currentUser->getCurrentUserId();
        }
        if (empty($data['UF_LOCATION'])) {
            $data['UF_LOCATION'] = $this->locationService->getCurrentLocation();
        }

        $items = $data['ITEMS'];
        unset($data['ITEMS']);

        /** @var OrderSubscribe $orderSubscribe */
        $orderSubscribe = $this->orderSubscribeRepository->dataToEntity($data, OrderSubscribe::class);
        $this->countNextDate($orderSubscribe);
        $this->orderSubscribeRepository->setEntity($orderSubscribe);
        if (!$this->orderSubscribeRepository->create()) {
            return false;
        }

        foreach ($items as $item) {
            /** @var OrderSubscribeItem $orderSubscribeItem */
            $orderSubscribeItem = $this->orderSubscribeItemRepository->dataToEntity($item, OrderSubscribeItem::class);
            $this->addSubscribeItem($orderSubscribe, $orderSubscribeItem);
        }

        return true;
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     * @return bool
     */
    public function update(array $data): bool
    {
        if(!empty($data['ITEMS'])){
            foreach ($data['ITEMS'] as $item) {
                /** @var OrderSubscribeItem $orderSubscribeItem */
                $orderSubscribeItem = $this->orderSubscribeItemRepository->dataToEntity($item, OrderSubscribeItem::class);
                $this->updateSubscribeItem($orderSubscribeItem);
            }
            unset($data['ITEMS']);
        }

        /** @var OrderSubscribe $entity */
        $entity = $this->orderSubscribeRepository->dataToEntity($data, OrderSubscribe::class);

        /** @var OrderSubscribe $updateEntity */
        $updateEntity = $this->orderSubscribeRepository->findById($entity->getId());
        if ($updateEntity->getUserId() !== $this->currentUser->getCurrentUserId()) {
            throw new SecurityException('не хватает прав доступа для совершения данной операции');
        }

        if ($entity->getUserId() === 0) {
            $entity->setUserId($updateEntity->getUserId());
        }

        $entity->setDateUpdate(new DateTime());

        return $this->orderSubscribeRepository->setEntity($entity)->update();
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     * @return bool
     */
    public function delete(int $id): bool
    {
        /** @var OrderSubscribe $deleteEntity */
        $deleteEntity = $this->orderSubscribeRepository->findById($id);
        if ($deleteEntity->getUserId() !== $this->currentUser->getCurrentUserId()) {
            throw new SecurityException('не хватает прав доступа для совершения данной операции');
        }

        $deleteEntityItems = $this->orderSubscribeItemRepository->findBySubscribe($id);
        /** @var OrderSubscribeItem $item */
        foreach ($deleteEntityItems as $item) {
            $this->deleteSubscribeItem($item->getId());
        }

        return $this->orderSubscribeRepository->delete($id);
    }

    /**
     * @param OrderSubscribe $orderSubscribe
     * @param OrderSubscribeItem $orderSubscribeItem
     * @return bool
     * @throws \Exception
     */
    public function addSubscribeItem(OrderSubscribe $orderSubscribe, OrderSubscribeItem $orderSubscribeItem): bool
    {
        if (!$orderSubscribe->getId()) {
            // TODO: Продумать механизм исключений
            throw new Exception('Добавлять товары можно только на существующую подписку');
        }

        $orderSubscribeItem->setSubscribeId($orderSubscribe->getId());
        $this->orderSubscribeItemRepository->setEntity($orderSubscribeItem);

        return $this->orderSubscribeItemRepository->create();
    }

    /**
     * @param OrderSubscribeItem $orderSubscribeItem
     * @return bool
     * @throws \Exception
     */
    public function updateSubscribeItem(OrderSubscribeItem $orderSubscribeItem): bool
    {
        if (!$orderSubscribeItem->getId()) {
            throw new Exception('Обновлять можно только существующие товары');
        }
        return $this->orderSubscribeItemRepository->setEntity($orderSubscribeItem)->update();
    }

    /**
     * @param OrderSubscribeItem $orderSubscribeItem
     * @return bool
     * @throws \Exception
     */
    public function deleteSubscribeItem($id): bool
    {
        return $this->orderSubscribeItemRepository->delete($id);
    }


    /**
     * @param OrderSubscribe $orderSubscribe
     * @throws \Bitrix\Main\ObjectException
     * @throws \Exception
     */
    public function countNextDate(OrderSubscribe &$orderSubscribe)
    {
        $freqs = $this->getFrequencies();
        $nextDate = $orderSubscribe->getNextDate();

        if(null === $nextDate){
            $nextDate = new DateTime();
        }

        switch ($orderSubscribe->getFrequency()){
            case $freqs['WEEK_1']:
                $nextDate->add("+1 week");
                break;
            case $freqs['WEEK_2']:
                $nextDate->add("+2 week");
                break;
            case $freqs['WEEK_3']:
                $nextDate->add("+3 week");
                break;
            case $freqs['MONTH_1']:
                $nextDate->add("+1 month");
                break;
            case $freqs['MONTH_2']:
                $nextDate->add("+2 month");
                break;
            case $freqs['MONTH_3']:
                $nextDate->add("+3 month");
                break;
            default:
                throw new Exception('Не найдена подходящая периодичность');
        }
        
        $orderSubscribe->setNextDate($nextDate);
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     * @return bool
     */
    public function getFrequencies(): array
    {
        if(null === $this->frequencies){
            $userFieldId = UserFieldTable::query()->setSelect(['ID', 'XML_ID'])->setFilter(
                [
                    'FIELD_NAME' => 'UF_FREQUENCY',
                    'ENTITY_ID' => 'HLBLOCK_' . HighloadHelper::getIdByName('OrderSubscribe'),
                ]
            )->exec()->fetch()['ID'];
            $userFieldEnum = new \CUserFieldEnum();
            $res = $userFieldEnum->GetList([], ['USER_FIELD_ID' => $userFieldId]);
            while ($item = $res->Fetch()) {
                $this->frequencies[$item['XML_ID']] = $item['ID'];
            }
        }

        return $this->frequencies;
    }
}