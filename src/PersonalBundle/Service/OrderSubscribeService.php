<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 25.03.2019
 * Time: 17:25
 */

namespace FourPaws\PersonalBundle\Service;


use Bitrix\Main\Security\SecurityException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem;
use FourPaws\PersonalBundle\Repository\OrderSubscribeItemRepository;
use FourPaws\PersonalBundle\Repository\OrderSubscribeRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use http\Exception\InvalidArgumentException;
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
     * OrderSubscribeService constructor.
     *
     * @param OrderSubscribeRepository     $orderSubscribeRepository
     * @param CurrentUserProviderInterface $currentUserProvider
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(
        OrderSubscribeRepository $orderSubscribeRepository,
        CurrentUserProviderInterface $currentUserProvider,
        LocationService $locationService
    ) {
        $this->orderSubscribeRepository = $orderSubscribeRepository;
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
        if(empty($data['ITEMS'])){
            throw new InvalidArgumentException('Для подписки на доставку необходимо передать список товаров');
        }
        if (empty($data['UF_USER_ID'])) {
            $data['UF_USER_ID'] = $this->currentUser->getCurrentUserId();
        }
        if (empty($data['UF_LOCATION'])) {
            $data['UF_LOCATION'] = $this->locationService->getCurrentRegionCode();
        }

        $items = $data['ITEMS'];
        unset($data['ITEMS']);

        /** @var OrderSubscribe $entity */
        $orderSubscribe = $this->orderSubscribeRepository->dataToEntity($data, OrderSubscribe::class);
        $this->orderSubscribeRepository->setEntity($orderSubscribe);
        if(!$this->orderSubscribeRepository->create()){
            return false;
        }

        /** @var OrderSubscribe $orderSubscribe */
        //$orderSubscribe = $this->orderSubscribeRepository->getEntityClass();

        foreach ($items as $item){
            /** @var OrderSubscribeItem $orderSubscribeItem */
            $orderSubscribeItem = $this->orderSubscribeItemRepository->dataToEntity($item, OrderSubscribeItem::class);
            $orderSubscribeItem->setSubscribeId($orderSubscribe->getId());
            $this->orderSubscribeItemRepository->setEntity($orderSubscribeItem);
            if(!$this->orderSubscribeItemRepository->create()){
                return false;
            }
        }

        return $orderSubscribe->getId();
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     * @return bool
     */
    public function update(array $data): bool
    {
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
        foreach($deleteEntityItems as $item){
            $this->orderSubscribeItemRepository->delete($item['ID']);
        }

        return $this->orderSubscribeRepository->delete($id);
    }
}