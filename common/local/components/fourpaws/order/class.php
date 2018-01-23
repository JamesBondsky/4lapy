<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Sale\BasketItem;
use FourPaws\App\Application;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
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
        /** @var BasketService $basketService */
        $basketService = Application::getInstance()->getContainer()->get(BasketService::class);
        $basket = $basketService->getBasket()->getOrderableItems();
        if ($basket->isEmpty()) {
            LocalRedirect('/cart');
        }

        /** @var CurrentUserProviderInterface $userService */
        $userService = \FourPaws\App\Application::getInstance()->getContainer()->get(
            CurrentUserProviderInterface::class
        );

        $order = null;
        $storage = null;
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
            if (!$storage = $this->orderService->getStorage()) {
                $this->abortResultCache();
                throw new Exception('Failed to initialize storage');
            }
        }

        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routeCollection */
        $routeCollection = $router->getRouteCollection();
        $routes = [
            'AUTH_VALIDATION'     => 'fourpaws_sale_ajax_order_validateauth',
            'DELIVERY_VALIDATION' => 'fourpaws_sale_ajax_order_validatedelivery',
            'PAYMENT_VALIDATION'  => 'fourpaws_sale_ajax_order_validatepayment',
        ];
        $ajaxUrl = [];
        foreach ($routes as $key => $name) {
            if (!$route = $routeCollection->get($name)) {
                continue;
            }
            $ajaxUrl[$key] = $route->getPath();
        }

        $this->arResult = [
            'ORDER'             => $order,
            'BASKET'            => $basket,
            'STORAGE'           => $storage,
            'URL'               => $ajaxUrl,
        ];

        return $this;
    }
}
