<?php

use FourPaws\App\Application as App;
use FourPaws\External\Manzana\Exception\ExecuteErrorException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;

class CStampsBalanceComponent extends \CBitrixComponent
{
    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthorizationService;

    /**
     * @var StampService
     */
    protected $stampService;

    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $container = App::getInstance()->getContainer();

        $this->userAuthorizationService = $container->get(UserAuthorizationInterface::class);
        $this->stampService = $container->get(StampService::class);
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    public function isAuthorized()
    {
        return $this->userAuthorizationService->isAuthorized();
    }

    /**
     * @return int
     * @throws ExecuteErrorException
     * @throws ExecuteException
     */
    public function getActiveStampsCount()
    {
        return $this->stampService->getActiveStampsCount();
    }
}
