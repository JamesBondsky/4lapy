<?php

namespace FourPaws\SaleBundle\Service;

use FourPaws\BitrixOrmBundle\Exception\NotFoundRepository;
use FourPaws\BitrixOrmBundle\Orm\BitrixOrm;
use FourPaws\External\ExpertsenderService;
use FourPaws\SaleBundle\Entity\ForgotBasket;
use FourPaws\SaleBundle\Exception\ForgotBasket\AlreadyExistsException;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToCreateException;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToDeleteException;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToUpdateException;
use FourPaws\SaleBundle\Exception\ForgotBasket\NotFoundException;
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
     * @param int $fuserId
     *
     * @return ForgotBasket
     * @throws NotFoundException
     */
    public function getTask(int $fuserId): ForgotBasket
    {
        return $this->forgotBasketRepository->findByFuserId($fuserId);
    }

    /**
     * @param ForgotBasket $task
     *
     * @return bool
     * @throws AlreadyExistsException
     * @throws FailedToCreateException
     */
    public function addTask(ForgotBasket $task): bool
    {
        if ($this->forgotBasketRepository->find($task->getFuserId())) {
            throw new AlreadyExistsException(\sprintf('Task for fuser #%s already exists', $task->getFuserId()));
        }

        if (!$this->forgotBasketRepository->create($task)) {
            throw new FailedToCreateException(\sprintf('Failed to create task for fuser #%s', $task->getFuserId()));
        }

        return true;
    }

    /**
     * @param ForgotBasket $task
     *
     * @return bool
     * @throws FailedToUpdateException
     */
    public function updateTask(ForgotBasket $task): bool
    {
        if (!$this->forgotBasketRepository->update($task)) {
            throw new FailedToUpdateException(\sprintf('Failed to update task with id #%s', $task->getId()));
        }

        return true;
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
}
