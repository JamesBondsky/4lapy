<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Context;
use Bitrix\Main\Request;
use Bitrix\Sale\Order;
use Bitrix\Iblock\Component\Tools;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Exception\NotAuthorizedException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderComponent extends \CBitrixComponent
{
    const DEFAULT_TEMPLATES_404 = [
        OrderService::AUTH_STEP     => 'index.php',
        OrderService::DELIVERY_STEP => 'delivery/',
        OrderService::PAYMENT_STEP  => 'payment/',
        OrderService::COMPLETE_STEP => 'complete/',
    ];

    /**
     * @var string
     */
    protected $currentStep;

    protected $stepOrder = [
        OrderService::AUTH_STEP,
        OrderService::DELIVERY_STEP,
        OrderService::PAYMENT_STEP,
        OrderService::COMPLETE_STEP,
    ];

    /** @var OrderService */
    protected $orderService;

    public function __construct($component = null)
    {
        $this->orderService = \FourPaws\App\Application::getInstance()->getContainer()->get(OrderService::class);
        parent::__construct($component);
    }

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {


        return parent::onPrepareComponentParams($params);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        global $APPLICATION;
        try {
            if ($this->startResultCache()) {
                $variables = [];
                $componentPage = CComponentEngine::ParseComponentPath(
                    $this->arParams['SEF_FOLDER'],
                    self::DEFAULT_TEMPLATES_404,
                    $variables
                );

                if (!$componentPage) {
                    LocalRedirect($this->arParams['SEF_FOLDER']);
                }

                $this->currentStep = $componentPage;

                if ($this->arParams['SET_TITLE'] === 'Y') {
                    $APPLICATION->SetTitle('Оформление заказа');
                }

                $this->prepareResult();

                $this->includeComponentTemplate($componentPage);
            }
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function prepareResult()
    {
        /** @var CurrentUserProviderInterface $userService */
        $userService = \FourPaws\App\Application::getInstance()->getContainer()->get(
            CurrentUserProviderInterface::class
        );

        $order = null;
        $storage = null;
        $errors = [];
        if ($this->currentStep === OrderService::COMPLETE_STEP) {
            /**
             * При переходе на страницу "спасибо за заказ" мы ищем заказ с переданным id
             */
            try {
                $userId = $userService->getCurrentUserId();
            } catch (NotAuthorizedException $e) {
                $userId = null;
            }
            try {
                $order = $this->orderService->getById(
                    $this->arParams['ORDER_ID'],
                    true,
                    $userId,
                    $this->arParams['HASH']
                );
            } catch (NotFoundException $e) {
                Tools::process404('', true, true, true);
            }
        } else {
            $request = Context::getCurrent()->getRequest();
            if (!$storage = $this->orderService->getStorage()) {
                $this->abortResultCache();
                throw new Exception('Failed to initialize storage');
            }

            /**
             * Если была отправлена форма, то мы ее проверяем и сохраняем в storage
             * При наличии ошибок отображаем их пользователю
             * При отсутствии таковых редиректим дальше
             */
            if ($request->isPost()) {
                $storage = $this->updateFromRequest($storage, $request);

                try {
                    $this->orderService->updateStorage($storage);
                    $nextStep = $this->getNextStep($this->currentStep);

                    /**
                     * Если форма заполнена до конца, мы создаем заказ и очищаем хранилище
                     */
                    if ($nextStep === OrderService::COMPLETE_STEP) {
                        if (!$order = $this->makeOrder($storage)) {
                            $this->abortResultCache();
                            throw new Exception('Failed to create order');
                        }
                    }

                    $hash = $order->getHash();
                    $this->redirect($nextStep, $order);
                } catch (OrderStorageValidationException $e) {
                    $errors = $e->getErrors();
                }
            }
        }
        $this->arResult = [
            'ORDER'   => $order,
            'STORAGE' => $storage,
            'ERRORS'  => $errors,
        ];

        return $this;
    }

    /**
     * @param string $step
     *
     * @return mixed
     */
    protected function getNextStep(string $step)
    {
        $key = array_search($step, $this->stepOrder, true);

        return $this->stepOrder[++$key];
    }

    protected function updateFromRequest(OrderStorage $storage, Request $request)
    {
        // @todo update storage

        return $storage;
    }

    /**
     * @param OrderStorage $storage
     *
     * @return bool|Order
     */
    protected function makeOrder(OrderStorage $storage)
    {
        // @todo create order
        return false;
    }

    protected function redirect(string $nextStep, $order)
    {
        /** @var UserAuthorizationInterface $userService */
        $userService = \FourPaws\App\Application::getInstance()->getContainer()->get(
            UserAuthorizationInterface::class
        );

        $url = $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[$nextStep];
        if ($order instanceof Order) {
            if (!$userService->isAuthorized()) {
                $hash = $order->getHash();
            }

            // @todo make url
        }

        LocalRedirect($url);
    }
}
