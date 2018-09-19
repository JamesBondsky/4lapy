<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrmBundle\Exception\NotFoundRepository;
use FourPaws\BitrixOrmBundle\Orm\BitrixOrm;
use FourPaws\SaleBundle\Dto\Notification\ForgotBasketNotification;
use FourPaws\SaleBundle\Entity\ForgotBasket;
use FourPaws\SaleBundle\Enum\ForgotBasketEnum;
use FourPaws\SaleBundle\Exception\BasketUserInitializeException;
use FourPaws\SaleBundle\Exception\ForgotBasket\AlreadyExistsException;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToCreateException;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToDeleteException;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToUpdateException;
use FourPaws\SaleBundle\Exception\ForgotBasket\NotFoundException;
use FourPaws\SaleBundle\Exception\ForgotBasket\UnknownTypeException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\Notification\UnknownMessageTypeException;
use FourPaws\SaleBundle\Repository\ForgotBasketRepository;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotFoundException as NotFoundUserException;
use FourPaws\UserBundle\Service\UserSearchInterface;

class ForgotBasketService
{
    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var ForgotBasketRepository
     */
    protected $forgotBasketRepository;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var BasketUserService
     */
    protected $basketUserService;

    /**
     * @var UserSearchInterface
     */
    protected $userSearch;

    /**
     * ForgotBasketService constructor.
     *
     * @param BasketService       $basketService
     * @param BasketUserService   $basketUserService
     * @param NotificationService $notificationService
     * @param BitrixOrm           $bitrixOrm
     * @param UserSearchInterface $userSearch
     *
     * @throws NotFoundRepository
     */
    public function __construct(
        BasketService $basketService,
        BasketUserService $basketUserService,
        NotificationService $notificationService,
        BitrixOrm $bitrixOrm,
        UserSearchInterface $userSearch
    )
    {
        $this->basketService = $basketService;
        $this->basketUserService = $basketUserService;
        $this->notificationService = $notificationService;
        $this->forgotBasketRepository = $bitrixOrm->getD7Repository(ForgotBasket::class);
        $this->userSearch = $userSearch;
    }

    /**
     * @param ForgotBasket $task
     *
     * @throws AlreadyExistsException
     * @throws FailedToCreateException
     * @throws FailedToUpdateException
     * @throws UnknownTypeException
     * @throws SystemException
     */
    public function saveTask(ForgotBasket $task): void
    {
        if ($task->getId()) {
            $this->updateTask($task);
        } else {
            try {
                $existingTask = $this->getTask($task->getUserId(), $task->getType());

                $task->setId($existingTask->getId());
                $this->updateTask($task);
            } catch (NotFoundException $e) {
                $this->addTask($task);
            }
        }
    }

    /**
     * @param int    $userId
     * @param string $type
     *
     * @return ForgotBasket
     * @throws NotFoundException
     * @throws UnknownTypeException
     * @throws SystemException
     */
    public function getTask(int $userId, string $type = ForgotBasketEnum::INTERVAL_NOTIFICATION): ForgotBasket
    {
        return $this->forgotBasketRepository->findByUserId($userId, $type);
    }

    /**
     * @param string $type
     * @param bool   $useDateFilter
     * @return Collection
     * @throws SystemException
     * @throws UnknownTypeException
     */
    public function getActiveTasks(string $type = ForgotBasketEnum::INTERVAL_NOTIFICATION, bool $useDateFilter)
    {
        return $this->forgotBasketRepository->getActive($type, $useDateFilter);
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws FailedToDeleteException
     */
    public function deleteTask(int $id): bool
    {
        if (!$this->forgotBasketRepository->delete($id)) {
            throw new FailedToDeleteException(\sprintf('Failed to delete task #%s', $id));
        }

        return true;
    }

    /**
     * @param ForgotBasket $task
     *
     * @throws AlreadyExistsException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws BasketUserInitializeException
     * @throws ConstraintDefinitionException
     * @throws FailedToCreateException
     * @throws FailedToUpdateException
     * @throws InvalidArgumentException
     * @throws InvalidIdentifierException
     * @throws NotFoundUserException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws UnknownTypeException
     * @throws \Exception
     * @throws UnknownMessageTypeException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function executeTask(ForgotBasket $task)
    {
        $user = $this->userSearch->findOne($task->getUserId());
        $basket = $this->basketService->getBasket(
            true,
            $this->basketUserService->getByUserId($task->getUserId())
        );

        $notification = new ForgotBasketNotification();
        $notification->setMessageType($task->getType())
                     ->setUser($user)
                     ->setBonusCount($this->basketService->getBasketBonus($user))
                     ->setBasket($basket);

        if ($this->notificationService->sendForgotBasketMessage($notification)) {
            /**
             * после напоминания создаем еще одно задание для отправки через трое суток
             */
            if ($task->getType() === ForgotBasketEnum::TYPE_NOTIFICATION) {
                $reminderTask = new ForgotBasket();
                $reminderTask->setType(ForgotBasketEnum::TYPE_REMINDER)
                             ->setUserId($task->getUserId())
                             ->setActive(true);
                $this->saveTask($reminderTask);
            }
        }

        $task->setDateExec(new \DateTime())
             ->setActive(false);
        $this->saveTask($task);
    }

    /**
     * @return string[]
     * @throws SystemException
     */
    public function getTypes(): array
    {
        return $this->forgotBasketRepository->getTypes();
    }

    /**
     * @param ForgotBasket $task
     *
     * @return bool
     * @throws AlreadyExistsException
     * @throws FailedToCreateException
     * @throws SystemException
     * @throws UnknownTypeException
     */
    protected function addTask(ForgotBasket $task): bool
    {
        if ($this->forgotBasketRepository->find($task->getUserId())) {
            throw new AlreadyExistsException(\sprintf('Task for user #%s already exists', $task->getUserId()));
        }

        $task->setType(
            $this->forgotBasketRepository->getTypeIdByCode(
                $task->getType()
            )
        );

        if (!$this->forgotBasketRepository->create($task)) {
            throw new FailedToCreateException(\sprintf('Failed to create task for user #%s', $task->getUserId()));
        }

        return true;
    }

    /**
     * @param ForgotBasket $task
     *
     * @return bool
     * @throws FailedToUpdateException
     * @throws SystemException
     * @throws UnknownTypeException
     */
    protected function updateTask(ForgotBasket $task): bool
    {
        $task->setType(
            $this->forgotBasketRepository->getTypeIdByCode(
                $task->getType()
            )
        );

        if (!$this->forgotBasketRepository->update($task)) {
            throw new FailedToUpdateException(\sprintf('Failed to update task with id #%s', $task->getId()));
        }

        return true;
    }
}
