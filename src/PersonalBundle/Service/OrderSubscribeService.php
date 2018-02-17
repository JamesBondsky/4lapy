<?php

namespace FourPaws\PersonalBundle\Service;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\ManzanaService;
use FourPaws\PersonalBundle\Repository\OrderRepository;
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
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;
    /** @var ManzanaService */
    private $manzanaService;

    /**
     * OrderSubscribeService constructor.
     *
     * @param OrderRepository $orderRepository
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->currentUser = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        $this->manzanaService = Application::getInstance()->getContainer()->get('manzana.service');
    }
}
