<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\PersonalBundle\Entity\Bonus;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetOrdersComponent extends CBitrixComponent
{
    /**
     * @var OrderService
     */
    private $orderService;

    /** @var UserAuthorizationInterface */
    private $authUserProvider;

    /** @var UserAuthorizationInterface */
    private $currentUserProvider;

    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
        $this->orderService = $container->get('order.service');
        $this->authUserProvider = $container->get(UserAuthorizationInterface::class);
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['PAGE_COUNT'] = 10;
        /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
        /** кешируем на сутки, можно будет увеличить если обновления будут не очень частые - чтобы лишний кеш не хранился */
        $params['CACHE_TIME'] = 24 * 60 * 60;
        return $params;
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     * @throws EmptyEntityClass
     * @throws SystemException
     * @throws IblockNotFoundException
     * @throws ObjectException
     * @throws ArgumentException
     * @throws \Exception
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        if (!$this->authUserProvider->isAuthorized()) {
            define('NEED_AUTH', true);

            return null;
        }

        $this->setFrameMode(true);

        /** получаем заказы из манзаны, кешируем на сутки */
        try {
            $userId = $this->currentUserProvider->getCurrentUserId();
            $manzanaOrders = $this->orderService->getManzanaOrders();
        } catch (NotAuthorizedException $e) {
            /** запрашиваем авторизацию */
            \define('NEED_AUTH', true);
            return null;
        } catch (ManzanaServiceException $e) {
            $manzanaOrders = new ArrayCollection();
        }

        // кешируем шаблон по номерам чеков из манзаны, ибо инфа в манзану должна передаваться всегда
        /** @noinspection PhpUndefinedVariableInspection */
        if ($this->startResultCache($this->arParams['CACHE_TIME'],
            ['manzanaOrders' => $manzanaOrders->getKeys(), 'USER_ID' => $userId])) {
            try {
                $this->arResult['ACTIVE_ORDERS'] = $this->orderService->getActiveSiteOrders();
                $allClosedOrders = $this->orderService->mergeAllClosedOrders($this->orderService->getClosedSiteOrders()->toArray(),
                    $manzanaOrders->toArray());
                /** Сортировка по дате и статусу общих заказов */
                $allClosedOrdersList = $allClosedOrders->toArray();
                usort($allClosedOrdersList, ['FourPawsPersonalCabinetOrdersComponent', 'sortByStatusAndDate']);
                /** имитация постранички */
                $nav = new PageNavigation('nav-orders');
                $nav->allowAllRecords(false)->setPageSize($this->arParams['PAGE_COUNT'])->initFromUri();
                $nav->setRecordCount($allClosedOrders->count());
                $this->arResult['CLOSED_ORDERS'] = new ArrayCollection(array_slice($allClosedOrdersList,
                    $nav->getOffset(), $nav->getPageSize(), true));
            } catch (NotAuthorizedException $e) {
                /** запрашиваем авторизацию */
                \define('NEED_AUTH', true);
                return null;
            }
            if ($nav instanceof PageNavigation) {
                $this->arResult['NAV'] = $nav;
            }
            $storeService = App::getInstance()->getContainer()->get('store.service');
            $this->arResult['METRO'] = new ArrayCollection($storeService->getMetroInfo());

            $this->includeComponentTemplate();
        }

        return true;
    }

    /**
     * @param Order $item1
     * @param Order $item2
     *
     * @return int
     */
    public function sortByStatusAndDate(Order $item1, Order $item2): int
    {
        if ($item1->getStatusSort() === $item2->getStatusSort()) {
            if ($item1->getDateInsert() > $item2->getDateInsert()) {
                return 1;
            }

            if ($item1->getDateInsert() < $item2->getDateInsert()) {
                return -1;
            }

            return 0;
        }

        if ($item1->getStatusSort() > $item2->getStatusSort()) {
            return 1;
        }

        return -1;
    }
}
