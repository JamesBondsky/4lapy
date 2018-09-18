<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Main\SystemException;
use FourPaws\BitrixOrmBundle\Exception\NotFoundRepository;
use FourPaws\BitrixOrmBundle\Orm\BitrixOrm;
use FourPaws\External\ExpertsenderService;
use FourPaws\SaleBundle\Entity\ForgotBasket;
use FourPaws\SaleBundle\Enum\ForgotBasketEnum;
use FourPaws\SaleBundle\Exception\ForgotBasket\AlreadyExistsException;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToCreateException;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToDeleteException;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToUpdateException;
use FourPaws\SaleBundle\Exception\ForgotBasket\NotFoundException;
use FourPaws\SaleBundle\Exception\ForgotBasket\UnknownTypeException;
use FourPaws\SaleBundle\Repository\ForgotBasketRepository;

class ForgotBasketService
{
    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var BasketUserService
     */
    protected $basketUserService;

    /**
     * @var ForgotBasketRepository
     */
    protected $forgotBasketRepository;

    /**
     * @var ExpertsenderService
     */
    protected $expertsenderService;

    /**
     * ForgotBasketService constructor.
     *
     * @param BasketService       $basketService
     * @param BasketUserService   $basketUserService
     * @param ExpertsenderService $expertsenderService
     * @param BitrixOrm           $bitrixOrm
     *
     * @throws NotFoundRepository
     */
    public function __construct(
        BasketService $basketService,
        BasketUserService $basketUserService,
        ExpertsenderService $expertsenderService,
        BitrixOrm $bitrixOrm
    )
    {
        $this->basketService = $basketService;
        $this->basketUserService = $basketUserService;
        $this->expertsenderService = $expertsenderService;
        $this->forgotBasketRepository = $bitrixOrm->getD7Repository(ForgotBasket::class);
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
     */
    public function getTask(int $userId, string $type = ForgotBasketEnum::INTERVAL_NOTIFICATION): ForgotBasket
    {
        return $this->forgotBasketRepository->findByUserId($userId, $type);
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
     */
    public function executeTask(ForgotBasket $task)
    {
        // @todo
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
