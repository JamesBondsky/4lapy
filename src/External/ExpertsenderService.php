<?php

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\FuserTable;
use Bitrix\Sale\Order;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Exception\ExpertsenderBasketEmptyException;
use FourPaws\External\Exception\ExpertsenderEmptyEmailException;
use FourPaws\External\Exception\ExpertsenderServiceApiException;
use FourPaws\External\Exception\ExpertsenderServiceBlackListException;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\External\Exception\ExpertsenderUserNotFoundException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\SaleBundle\Dto\Fiscalization\Item;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\PaymentService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\NotFoundException as UserNotFoundException;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use LinguaLeo\ExpertSender\Entities\Property;
use LinguaLeo\ExpertSender\Entities\Receiver;
use LinguaLeo\ExpertSender\Entities\Snippet;
use LinguaLeo\ExpertSender\ExpertSender;
use LinguaLeo\ExpertSender\ExpertSenderException;
use LinguaLeo\ExpertSender\Request\AddUserToList;
use LinguaLeo\ExpertSender\Results\ApiResult;
use LinguaLeo\ExpertSender\Results\UserIdResult;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ExpertsenderService
 *
 * @package FourPaws\External
 */
class ExpertsenderService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const FORGOT_BASKET_TO_CLOSE_SITE = 1;
    public const FORGOT_BASKET_AFTER_TIME = 2;

    protected const MAIN_LIST_MODE = 'AddAndUpdate';
    protected const MAIN_LIST_ID = 178;
    protected const MAIN_LIST_PROP_HASH_ID = 10;
    protected const MAIN_LIST_PROP_SUBSCRIBE_ID = 23;
    protected const MAIN_LIST_PROP_REGISTER_ID = 47;
    protected const MAIN_LIST_PROP_IP_ID = 48;

    protected $client;
    private $guzzleClient;
    private $key;
    private $url;

    /**
     * ExpertsenderService constructor.
     *
     * @throws ApplicationCreateException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */

    public const FORGOT_BASKET_LIST_ID = 7765;//old 7115
    public const FORGOT_BASKET2_LIST_ID = 7767;//old 7117
    public const CHANGE_EMAIL_LIST_ID = 7766;//old 7070
    public const CHANGE_EMAIL_TO_NEW_EMAIL_LIST = 7768;//old 7071
    public const SUBSCRIBE_EMAIL_UNDER_3_WEEK_LIST_ID = 7769;//old 7197
    public const SUBSCRIBE_EMAIL_UNDER_3_DAYS_LIST_ID = 7773;//old 7198
    public const NEW_ORDER_PAY_LIST_ID = 7774;//old 7103
    public const NEW_ORDER_NOT_PAY_LIST_ID = 7775;//old 7104
    public const NEW_ORDER_NOT_REG_PAY_LIST_ID = 7776;//old 7150
    public const NEW_ORDER_NOT_REG_NOT_PAY_LIST_ID = 7777;//old 7148
    public const COMPLETE_ORDER_LIST_ID = 7778;//old 7122
    public const FORGOT_PASSWORD_LIST_ID = 7779;//old 7072
    public const CHANGE_PASSWORD_LIST_ID = 7780;//old 7073

    public const BLACK_LIST_ERROR_CODE = 400;
    public const BLACK_LIST_ERROR_MESSAGE = 'Subscriber is blacklisted.';

    public function __construct()
    {
        $client = new Client();
        $this->guzzleClient = $client;

        [$url, $key] = \array_values(Application::getInstance()->getContainer()->getParameter('expertsender'));
        $this->key = $key;
        $this->url = $url;
        $this->client = new ExpertSender($url, $key, $client);
    }

    /**
     * @param User  $user
     * @param array $params
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ExpertsenderServiceException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function sendEmailAfterRegister(User $user, array $params = []): bool
    {
        if (!isset($params['isReg'])) {
            $params['isReg'] = true;
        }
        if (!isset($params['type'])) {
            $params['type'] = 'email_register';
        }
        if (!isset($params['subscribe'])) {
            $params['subscribe'] = false;
        }

        if ($user->hasEmail()) {
            $addUserToList = new AddUserToList();
            $addUserToList->setForce(true);
            $addUserToList->setMode(static::MAIN_LIST_MODE);
            $addUserToList->setTrackingCode('reg_form');
            $addUserToList->setListId(static::MAIN_LIST_ID);
            $addUserToList->setEmail($user->getEmail());
            $addUserToList->setFirstName($user->getName());
            $addUserToList->setLastName($user->getLastName());
            /** флаг подписки на новости */
            $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_SUBSCRIBE_ID, 'boolean',
                json_encode($params['subscribe'])));
            /** флаг регистрации */
            $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_REGISTER_ID, 'boolean', json_encode($params['isReg'])));
            try {
                /** хеш строка для подтверждения мыла */
                /** @var ConfirmCodeService $confirmService */
                $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $confirmService::setGeneratedHash($user->getEmail(), $params['type']);
                $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_HASH_ID, 'string',
                    $confirmService::getGeneratedCode($params['type'])));
                unset($generatedHash, $confirmService);
                /** ip юзверя */
                $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_IP_ID, 'string',
                    BitrixApplication::getInstance()->getContext()->getServer()->get('REMOTE_ADDR')));
                $this->addUserToList($addUserToList);
                return true;
            } catch (Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws ExpertsenderServiceException
     * @throws GuzzleException
     * @throws ExpertSenderException
     */
    public function sendChangePasswordByProfile(User $user): bool
    {
        if($user->hasEmail()) {
            $transactionId = self::CHANGE_PASSWORD_LIST_ID;

            $email = $user->getEmail();
            $userId = $user->getId();

            $this->log()->info(
                __FUNCTION__,
                [
                    'email' => $email,
                    'transactionId' => $transactionId,
                    'userId' => $userId,
                ]
            );

            $this->sendSystemTransactional($transactionId, $email);
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param string $backUrl
     *
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws Exception
     * @throws ExpertsenderServiceException
     * @throws GuzzleException
     * @throws SystemException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     */
    public function sendForgotPassword(User $user, string $backUrl = ''): bool
    {
        if ($user->hasEmail()) {
            try {
                $transactionId = self::FORGOT_PASSWORD_LIST_ID;

                /** хеш строка для подтверждения мыла */
                /** @var ConfirmCodeService $confirmService */
                $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $email = $user->getEmail();
                $userId = $user->getId();
                $confirmService::setGeneratedHash($email, 'email_forgot');
                $backUrlText = !empty($backUrl) ? '&backurl=' . $backUrl . '&user_id=' . $userId : '';
                $snippets = [
                    new Snippet('user_name', $user->getName() ?: $user->getFullName(), true),
                    new Snippet('link',
                        (new FullHrefDecorator('/personal/forgot-password/?hash=' . $confirmService::getGeneratedCode('email_forgot') . '&email=' . $email . $backUrlText))->getFullPublicPath(),
                        true),
                ];

                $this->log()->info(
                    __FUNCTION__,
                    [
                        'email' => $email,
                        'transactionId' => $transactionId,
                        'userId' => $userId,
                        'snippets' => implode(
                            '; ',
                            array_map(
                                function($snp) {
                                    return $snp instanceof Snippet ? $snp->getName().': '.$snp->getValue() : '-';
                                },
                                $snippets
                            )
                        ),
                    ]
                );

                $this->sendSystemTransactional($transactionId, $email, $snippets);
                return true;
            } catch (Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return false;
    }

    /**
     * @param User $oldUser
     * @param User $curUser
     * @return bool
     * @throws ExpertSenderException
     * @throws ExpertsenderServiceException
     * @throws GuzzleException
     */
    public function sendChangeEmail(User $oldUser, User $curUser): bool
    {
        $continue = true;
        $expertSenderId = 0;
        $hasNewEmailInSender = false;
        $transactionIdOld = self::CHANGE_EMAIL_LIST_ID;
        $transactionIdNew = self::CHANGE_EMAIL_TO_NEW_EMAIL_LIST;

        /** проверяем наличие новой почты в сендере */
        $curUserEmail = $curUser->getEmail();
        if ($curUser->hasEmail()) {
            try {
                $this->getUserId($curUserEmail);
                $hasNewEmailInSender = true;
            } catch (ExpertsenderServiceApiException $e) {
            }
        }

        $oldUserEmail = $oldUser->getEmail();
        if ($oldUser->hasEmail()) {

            $this->log()->info(
                __FUNCTION__,
                [
                    'curUserEmail' => $curUserEmail,
                    'oldUserEmail' => $oldUserEmail,
                    'transactionIdOld' => $transactionIdOld,
                    'oldUserId' => $oldUser->getId(),
                    'curUserId' => $curUser->getId(),
                ]
            );

            $continue = false;
            /** отправка почты на старый email */
            try {
                $this->sendSystemTransactional($transactionIdOld, $oldUserEmail);
                $continue = true;
            } catch (ExpertsenderServiceApiException $e) {
            }

            if (!$hasNewEmailInSender) {
                /** получение id подписчика по старому email */
                try {
                    $expertSenderId = $this->getUserId($oldUserEmail)->getId();
                } catch (ExpertsenderServiceApiException $e) {
                }

                $userIdResult = $this->client->getUserId($oldUserEmail);
                if ($userIdResult->isOk()) {
                    $expertSenderId = $userIdResult->getId();
                }
            }
        }

        if ($continue && $curUser->hasEmail()) {
            try {
                if (!$hasNewEmailInSender) {
                    $continue = false;
                    if ($expertSenderId) {
                        $addUserToList = new AddUserToList();
                        $addUserToList->setForce(true);
                        $addUserToList->setMode(static::MAIN_LIST_MODE);
                        $addUserToList->setTrackingCode('change_email');
                        $addUserToList->setListId(static::MAIN_LIST_ID);
                        $addUserToList->setEmail($curUserEmail);
                        $addUserToList->setId($expertSenderId);

                        $addUserToList->setName($curUser->getName());
                        $addUserToList->setLastName($curUser->getLastName());
                        /** ip юзверя */
                        $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_IP_ID, 'string',
                            BitrixApplication::getInstance()->getContext()->getServer()->get('REMOTE_ADDR')));
                        $this->addUserToList($addUserToList);
                        $continue = true;
                    } else {
                        /** если нет старой почты или не нашли на сайте регистрируем в сендере */
                        if ($this->sendEmailAfterRegister($curUser, ['isReg' => false, 'type' => 'email_change_email'])) {
                            $continue = true;
                        }
                    }
                }

                if ($continue) {
                    $this->log()->info(
                        __FUNCTION__,
                        [
                            'curUserEmail' => $curUserEmail,
                            'oldUserEmail' => $oldUserEmail,
                            'transactionIdNew' => $transactionIdNew,
                            'oldUserId' => $oldUser->getId(),
                            'curUserId' => $curUser->getId(),
                        ]
                    );

                    /** отправка почты на новый email, отправляем именно при смене и при регистрации */
                    $this->sendSystemTransactional($transactionIdNew, $curUserEmail);
                    return true;
                }
            } catch (Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return false;
    }

    /**
     * @param User $curUser
     *
     * @return bool
     * @throws ExpertsenderServiceException
     * @throws SystemException
     */
    public function changeUserData(User $curUser): bool
    {
        $expertSenderId = 0;
        if ($curUser->hasEmail()) {
            try {
                $expertSenderId = $this->getUserId($curUser->getEmail())->getId();
            } catch (ExpertsenderServiceApiException $e) {
            }
        }
        if ($expertSenderId && $curUser->hasEmail()) {
            $addUserToList = new AddUserToList();
            $addUserToList->setForce(true);
            $addUserToList->setMode(static::MAIN_LIST_MODE);
            $addUserToList->setTrackingCode('change_user_data');
            $addUserToList->setListId(static::MAIN_LIST_ID);
            $addUserToList->setId($expertSenderId);

            $addUserToList->setEmail($curUser->getEmail());
            $addUserToList->setName($curUser->getName());
            $addUserToList->setLastName($curUser->getLastName());
            /** ip юзверя */
            $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_IP_ID, 'string',
                BitrixApplication::getInstance()->getContext()->getServer()->get('REMOTE_ADDR')));

            $this->addUserToList($addUserToList);
        }

        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws ExpertsenderServiceException
     * @throws GuzzleException
     * @throws SystemException
     */
    public function sendEmailSubscribeNews(User $user): bool
    {
        if ($user->hasEmail()) {
            $expertSenderId = 0;
            try {
                $expertSenderId = $this->getUserId($user->getEmail())->getId();
            } catch (ExpertsenderServiceApiException $e) {
            }

            if ($expertSenderId) {
                $addUserToList = new AddUserToList();
                $addUserToList->setForce(true);
                $addUserToList->setMode(static::MAIN_LIST_MODE);
                $addUserToList->setTrackingCode('subscribe');
                $addUserToList->setListId(static::MAIN_LIST_ID);
                $addUserToList->setEmail($user->getEmail());
                $addUserToList->setId($expertSenderId);

                /** флаг подписки на новости */
                $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_SUBSCRIBE_ID, 'boolean', json_encode(true)));
                /** ip юзверя */
                $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_IP_ID, 'string',
                    BitrixApplication::getInstance()->getContext()->getServer()->get('REMOTE_ADDR')));


                $this->addUserToList($addUserToList);
            }

            /** если не нашли id по почте регистрируем в сендере */
            return $this->sendEmailAfterRegister($user,
                    ['isReg' => false, 'type' => 'email_subscribe', 'subscribe' => true]);
        }
        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws ExpertsenderServiceException
     * @throws GuzzleException
     * @throws SystemException
     */
    public function sendEmailUnSubscribeNews(User $user): bool
    {
        if ($user->hasEmail()) {
            $expertSenderId = 0;
            try {
                $expertSenderId = $this->getUserId($user->getEmail())->getId();
            } catch (ExpertsenderServiceApiException $e) {
            }

            if ($expertSenderId) {
                $addUserToList = new AddUserToList();
                $addUserToList->setForce(true);
                $addUserToList->setMode(static::MAIN_LIST_MODE);
                $addUserToList->setTrackingCode('unsubscribe');
                $addUserToList->setListId(static::MAIN_LIST_ID);
                $addUserToList->setId($expertSenderId);
                $addUserToList->setEmail($user->getEmail());
                /** флаг подписки на новости */
                $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_SUBSCRIBE_ID, 'boolean', json_encode(false)));
                /** ip юзверя */
                $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_IP_ID, 'string',
                    BitrixApplication::getInstance()->getContext()->getServer()->get('REMOTE_ADDR')));

                $this->addUserToList($addUserToList);
            }
            return true;
        }
        return false;
    }

    /**
     * @param string $email
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function checkConfirmEmailSubscribe(string $email): bool
    {
        $response = $this->guzzleClient->get($this->url . '/Api/Subscribers?apiKey=' . $this->key . '&email=' . $email . '&option=Short');
        $activeLists = [];
        if ($response->getStatusCode() === 200) {
            $xml = new \SimpleXMLElement($response->getBody()->getContents());
            if (!(bool)$xml->Data->BlackList) {
                foreach ((array)$xml->Data->StateOnLists as $StateOnList) {
                    if ((string)$StateOnList->Status === 'Active') {
                        $activeLists[] = (int)$StateOnList->ListId;
                    }
                }
            }
            unset($xml);
        }

        if (\in_array(static::MAIN_LIST_ID, $activeLists, true)) {
            return true;
        }
        return false;
    }

    /**
     * @param Order $order
     * @return int
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws Exception
     * @throws ExpertsenderEmptyEmailException
     * @throws ExpertsenderServiceException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws UserNotFoundException
     */
    public function sendOrderNewEmail(Order $order): int
    {
        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        $properties = $orderService->getOrderPropertiesByCode($order, [
            'NAME',
            'DELIVERY_DATE',
            'PHONE',
            'USER_REGISTERED',
            'COM_WAY',
            'EMAIL',
        ]);

        /**
         * Не отправляем письма для заказов в 1 клик
         */
        if ($properties['COM_WAY'] === OrderPropertyService::COMMUNICATION_ONE_CLICK) {
            return 0;
        }

        $email = $properties['EMAIL'];
        if (empty($email)) {
            throw new ExpertsenderEmptyEmailException('order email is empty');
        }

        $properties['BONUS_COUNT'] = $orderService->getOrderBonusSum($order);

        $address = $orderService->getOrderDeliveryAddress($order);
        if ($orderService->getOrderDeliveryCode($order) === DeliveryService::INNER_PICKUP_CODE) {
            $address .= ' Внимание! Заказ необходимо забрать в течение 3х дней';
        }

        $snippets = [
            new Snippet('Order_number', $order->getField('ACCOUNT_NUMBER')),
            new Snippet('user_name', $properties['NAME']),
            new Snippet('delivery_address', $address),
            new Snippet('delivery_date', $properties['DELIVERY_DATE']),
            new Snippet('tel_number', PhoneHelper::formatPhone($properties['PHONE'])),
            new Snippet('delivery_cost', $order->getDeliveryPrice()),
            new Snippet('total_bonuses', (int)$properties['BONUS_COUNT']),
            new Snippet('order_date', $order->getDateInsert()->format('d.m.Y')),
        ];

        $isOnlinePayment = $orderService->isOnlinePayment($order);
        if ($properties['USER_REGISTERED'] === BitrixUtils::BX_BOOL_TRUE) {
            // зарегистрированный пользователь
            if ($isOnlinePayment) {
                // онлайн-оплата
                $transactionId = self::NEW_ORDER_PAY_LIST_ID;
            } else {
                // оплата при получении
                $transactionId = self::NEW_ORDER_NOT_PAY_LIST_ID;
            }
        } else {
            // незарегистрированный пользователь
            /* @todo вынести из сессии? */
            $snippets[] = new Snippet('login', $_SESSION['NEW_USER']['LOGIN']);
            $snippets[] = new Snippet('password', $_SESSION['NEW_USER']['PASSWORD']);
            if ($isOnlinePayment) {
                // онлайн-оплата
                $transactionId = self::NEW_ORDER_NOT_REG_PAY_LIST_ID;
            } else {
                // оплата при получении
                $transactionId = self::NEW_ORDER_NOT_REG_NOT_PAY_LIST_ID;
            }
        }

        $items = $this->getAltProductsItems($order);
        $items = '<Products>' . implode('', $items) . '</Products>';
        $snippets[] = new Snippet('alt_products', $items, true);

        $this->log()->info(
            __FUNCTION__,
            [
                'email' => $email,
                'transactionId' => $transactionId,
                'orderId' => $order->getId(),
                'snippets' => implode(
                    '; ',
                    array_map(
                        function($snp) {
                            return $snp instanceof Snippet ? $snp->getName().': '.$snp->getValue() : '-';
                        },
                        $snippets
                    )
                ),
            ]
        );

        $this->sendSystemTransactional($transactionId, $email, $snippets);
        return $transactionId;
    }

    /**
     * @param Order $order
     * @return int
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws Exception
     * @throws ExpertsenderEmptyEmailException
     * @throws ExpertsenderServiceException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function sendOrderCompleteEmail(Order $order): int
    {
        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);

        /**
         * Не отправляем письма для заказов в 1 клик
         */
        if ($orderService->getOrderPropertyByCode($order,
                'COM_WAY')->getValue() === OrderPropertyService::COMMUNICATION_ONE_CLICK) {
            return 0;
        }

        $email = $orderService->getOrderPropertyByCode($order, 'EMAIL')->getValue();
        if (empty($email)) {
            throw new ExpertsenderEmptyEmailException('order email is empty');
        }

        $snippets = [
            new Snippet('Order_number', $order->getField('ACCOUNT_NUMBER')),
            new Snippet(
                'user_name',
                $orderService->getOrderPropertyByCode(
                    $order,
                    'NAME'
                )->getValue()
            ),
            new Snippet(
                'delivery_address',
                $orderService->getOrderDeliveryAddress($order)
            ),
        ];

        $transactionId = self::COMPLETE_ORDER_LIST_ID;

        $this->log()->info(
            __FUNCTION__,
            [
                'email' => $email,
                'transactionId' => $transactionId,
                'orderId' => $order->getId(),
                'snippets' => implode(
                    '; ',
                    array_map(
                        function($snp) {
                            return $snp instanceof Snippet ? $snp->getName().': '.$snp->getValue() : '-';
                        },
                        $snippets
                    )
                ),
            ]
        );

        $this->sendSystemTransactional($transactionId, $email, $snippets);
        return $transactionId;
    }

    /**
     * Брошенная корзина
     *
     * @param Basket $basket
     * @param int    $type
     * type = 1 - отправка после закрытия спустя 3 часа
     * type = 2 - отправка спустя 3 дня если не было измениней после типа 1
     *
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ExpertsenderServiceBlackListException
     * @throws ExpertsenderUserNotFoundException
     * @throws ExpertsenderEmptyEmailException
     * @throws ExpertsenderBasketEmptyException
     * @throws ExpertsenderServiceException
     * @throws ExpertSenderException
     */
    public function sendForgotBasket(Basket $basket, int $type): bool
    {
        switch ($type) {
            case static::FORGOT_BASKET_TO_CLOSE_SITE:
                $transactionId = self::FORGOT_BASKET_LIST_ID;
                break;
            case static::FORGOT_BASKET_AFTER_TIME:
                $transactionId = self::FORGOT_BASKET2_LIST_ID;
                break;
            default:
                $transactionId = 0;
        }
        if ($transactionId === 0) {
            throw new ExpertsenderServiceException('unknown forgotBasket time');
        }
        $snippets = [];

        $container = Application::getInstance()->getContainer();
        /** @var CurrentUserProviderInterface $userService */
        $userService = $container->get(CurrentUserProviderInterface::class);

        $fuserId = $basket->getFUserId();

        $user = $userService->getUserRepository()->find(FuserTable::getUserById($fuserId));
        if ($user === null) {
            throw new ExpertsenderUserNotFoundException('user not found');
        }
        if (!$user->hasEmail()) {
            throw new ExpertsenderEmptyEmailException('email is empty');
        }

        $email = $user->getEmail();

        /** @var BasketService $orderService */
        $basketService = $container->get(BasketService::class);

        $snippets[] = new Snippet('user_name', $user->getName() ?: $user->getFullName());
        $snippets[] = new Snippet('total_bonuses', (int)$basketService->getBasketBonus($user));

        $items = $this->getAltProductsItemsByBasket($basket);
        if (empty($items)) {
            throw new ExpertsenderBasketEmptyException('basket is empty');
        }
        $items = '<Products>' . implode('', $items) . '</Products>';
        $snippets[] = new Snippet('alt_products', $items, true);

        try {
            $this->sendSystemTransactional($transactionId, $email, $snippets);
        } catch (ExpertsenderServiceApiException | ExpertsenderServiceException $e) {
            $message = $e->getMessage();
            /** чекаем на черный список */
            if ($this->isBlackListed($message)) {
                throw new ExpertsenderServiceBlackListException($message, $e->getCode(), $e, $e->getMethod(),
                    $e->getParameters());
            }
            if ($e instanceof ExpertsenderServiceApiException) {
                throw new ExpertsenderServiceApiException($message, $e->getCode(), $e, $e->getMethod(),
                    $e->getParameters());
            } else {
                throw new ExpertsenderServiceException($message, $e->getCode(), $e, $e->getMethod(),
                    $e->getParameters());
            }
        }

        return true;
    }

    /**
     * @param Basket $basket
     *
     * @return array
     */
    protected function getAltProductsItemsByBasket(Basket $basket): array
    {
        $items = [];
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $currentOffer = OfferQuery::getById((int)$basketItem->getProductId());
            if ($currentOffer === null) {
                throw new NotFoundException(sprintf('Не найден товар %s', $basketItem->getProductId()));
            }
            $item = '';
            $item .= '<Product>';
            $item .= '<Name>' . $basketItem->getField('NAME') . '</Name>';
            $item .= '<PicUrl>' . new FullHrefDecorator((string)$currentOffer->getImages()->first()) . '</PicUrl>';
            $item .= '<Link>' . new FullHrefDecorator($currentOffer->getDetailPageUrl()) . '</Link>';
            $item .= '<Price1>' . $basketItem->getBasePrice() . '</Price1>';
            $item .= '<Price2>' . $basketItem->getPrice() . '</Price2>';
            $item .= '<Amount>' . $basketItem->getQuantity() . '</Amount>';
            $item .= '</Product>';
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param Order $order
     * @return array
     * @throws ApplicationCreateException
     * @throws ExpertsenderServiceException
     * @throws ObjectNotFoundException
     */
    protected function getAltProductsItems(Order $order): array
    {
        /** @var PaymentService $paymentService */
        $paymentService = Application::getInstance()->getContainer()->get(PaymentService::class);
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        $offers = $orderService->getOrderProducts($order);

        $fiscalization = $paymentService->getFiscalization($order, 0, false);
        $items = [];
        try {
            $basketItems = $fiscalization->getFiscal()->getOrderBundle()->getCartItems()->getItems();
            /** @var Item $basketItem */
            foreach ($basketItems as $basketItem) {
                if (mb_strpos($basketItem->getCode(), 'DELIVERY') !== false) {
                    continue;
                }
                $currentOffer = null;
                /** @var Offer $offer */
                foreach ($offers as $offer) {
                    if ($offer->getId() === (int)$basketItem->getCode()) {
                        $currentOffer = $offer;
                    }
                }
                if (!$currentOffer) {
                    throw new NotFoundException(sprintf('Не найден товар %s', $basketItem->getCode()));
                }
                $link = ($currentOffer->getXmlId()[0] === '3') ? '' : new FullHrefDecorator($currentOffer->getDetailPageUrl());
                $item = '';
                $item .= '<Product>';
                $item .= '<Name>' . $currentOffer->getName(). '</Name>';
                $item .= '<PicUrl>' . new FullHrefDecorator((string)$currentOffer->getImages()->first()) . '</PicUrl>';
                $item .= '<Link>' . $link . '</Link>';
                $item .= '<Price1>' . $currentOffer->getOldPrice() . '</Price1>';
                $item .= '<Price2>' . ($basketItem->getPrice() / 100) . '</Price2>';
                $item .= '<Amount>' . $basketItem->getQuantity()->getValue() . '</Amount>';
                $item .= '</Product>';
                $items[] = $item;
            }
        } catch (NotFoundException $e) {
            throw new ExpertsenderServiceException($e->getMessage());
        }

        return $items;
    }

    /**
     * Оформлена подписка на доставку
     *
     * @param OrderSubscribe $orderSubscribe
     * @return int
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws Exception
     * @throws ExpertsenderEmptyEmailException
     * @throws ExpertsenderServiceException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws UserNotFoundException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\NotFoundException
     */
    public function sendOrderSubscribedEmail(OrderSubscribe $orderSubscribe): int
    {
        $transactionId = self::SUBSCRIBE_EMAIL_UNDER_3_WEEK_LIST_ID;
        $snippets = [];

        $personalOrder = $orderSubscribe->getOrder();
        $email = $personalOrder->getPropValue('EMAIL');
        if (empty($email)) {
            throw new ExpertsenderEmptyEmailException('order email is empty');
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        $order = $personalOrder->getBitrixOrder();

        $snippets[] = new Snippet('user_name', htmlspecialcharsbx($personalOrder->getPropValue('NAME')));
        $snippets[] = new Snippet('delivery_address', htmlspecialcharsbx($orderService->getOrderDeliveryAddress($order)));
        $snippets[] = new Snippet('delivery_date', htmlspecialcharsbx($orderSubscribe->getDateStartFormatted()));
        $snippets[] = new Snippet('delivery_period', htmlspecialcharsbx($orderSubscribe->getDeliveryTimeFormattedRu()));
        $snippets[] = new Snippet('tel_number', PhoneHelper::formatPhone($personalOrder->getPropValue('PHONE')));
        $snippets[] = new Snippet('total_bonuses', (int)$orderService->getOrderBonusSum($order));
        $snippets[] = new Snippet('delivery_cost', (float)$order->getShipmentCollection()->getPriceDelivery());

        $items = $this->getAltProductsItems($order);
        $items = '<Products>' . implode('', $items) . '</Products>';
        $snippets[] = new Snippet('alt_products', $items, true);

        $this->log()->info(
            __FUNCTION__,
            [
                'email' => $email,
                'transactionId' => $transactionId,
                'orderId' => $personalOrder->getId(),
                'snippets' => implode(
                    '; ',
                    array_map(
                        function($snp) {
                            return $snp instanceof Snippet ? $snp->getName().': '.$snp->getValue() : '-';
                        },
                        $snippets
                    )
                ),
            ]
        );

        $this->sendSystemTransactional($transactionId, $email, $snippets);

        return $transactionId;
    }

    /**
     * Информация о предстоящем заказе по подписке (только что созданном)
     *
     * @param Order $order
     * @return int
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws Exception
     * @throws ExpertsenderEmptyEmailException
     * @throws ExpertsenderServiceException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws UserNotFoundException
     */
    public function sendOrderSubscribeOrderNewEmail(Order $order): int
    {
        $transactionId = self::SUBSCRIBE_EMAIL_UNDER_3_DAYS_LIST_ID;
        $snippets = [];

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);

        $properties = $orderService->getOrderPropertiesByCode(
            $order,
            [
                'EMAIL',
                'NAME',
                'DELIVERY_DATE',
                'DELIVERY_INTERVAL',
                'PHONE',
            ]
        );

        $properties['EMAIL'] = $properties['EMAIL'] ?? '';
        $properties['NAME'] = $properties['NAME'] ?? '';
        $properties['DELIVERY_DATE'] = $properties['DELIVERY_DATE'] ?? '';
        $properties['DELIVERY_INTERVAL'] = $properties['DELIVERY_INTERVAL'] ?? '';
        $properties['PHONE'] = $properties['PHONE'] ?? '';

        $email = $properties['EMAIL'];
        if (empty($email)) {
            throw new ExpertsenderEmptyEmailException('order email is empty');
        }

        $snippets[] = new Snippet('user_name', htmlspecialcharsbx($properties['NAME']));
        $snippets[] = new Snippet('delivery_address', htmlspecialcharsbx($orderService->getOrderDeliveryAddress($order)));
        $snippets[] = new Snippet('delivery_date', htmlspecialcharsbx($properties['DELIVERY_DATE']));
        $snippets[] = new Snippet('delivery_time', htmlspecialcharsbx($properties['DELIVERY_INTERVAL']));
        $snippets[] = new Snippet('tel_number', $properties['PHONE'] !== '' ? PhoneHelper::formatPhone($properties['PHONE']) : '');
        $snippets[] = new Snippet('total_bonuses', (int)$orderService->getOrderBonusSum($order));
        $snippets[] = new Snippet('delivery_cost', (float)$order->getShipmentCollection()->getPriceDelivery());

        $items = $this->getAltProductsItems($order);
        $items = '<Products>' . implode('', $items) . '</Products>';
        $snippets[] = new Snippet('alt_products', $items, true);

        $this->log()->info(
            __FUNCTION__,
            [
                'email' => $email,
                'transactionId' => $transactionId,
                'orderId' => $order->getId(),
                'snippets' => implode(
                    '; ',
                    array_map(
                        function($snp) {
                            return $snp instanceof Snippet ? $snp->getName().': '.$snp->getValue() : '-';
                        },
                        $snippets
                    )
                ),
            ]
        );

        $this->sendSystemTransactional($transactionId, $email, $snippets);

        return $transactionId;
    }


    /**
     * @param int    $transactionId
     * @param string $email
     * @param array  $snippets
     *
     * @return ApiResult
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     * @throws ExpertSenderException
     */
    protected function sendSystemTransactional(int $transactionId, string $email, array $snippets = []): ApiResult
    {
        return $this->sendRequest(
            'sendSystemTransactional',
            [
                $transactionId,
                new Receiver($email),
                $snippets
            ]
        );
    }

    /**
     * @param AddUserToList $addUserToList
     *
     * @return ApiResult
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     */
    protected function addUserToList(AddUserToList $addUserToList): ApiResult
    {
        return $this->sendRequest('addUserToList', [$addUserToList]);
    }

    /**
     * @param string $email
     *
     * @return UserIdResult
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     */
    protected function getUserId(string $email): UserIdResult
    {
        return $this->sendRequest('getUserId', [$email]);
    }

    /**
     * @param $name
     * @param $parameters
     *
     * @return ApiResult|UserIdResult
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     */
    protected function sendRequest($name, $parameters)
    {
        try {
            /** @var ApiResult $apiResult */
            $apiResult = $this->client->$name(...$parameters);
        } catch (BadResponseException | GuzzleException | Exception $e) {
            if($e instanceof BadResponseException) {
                $message = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            } else {
                $message = $e->getMessage();
            }
            throw new ExpertsenderServiceException(
                $message,
                $e->getCode(),
                $e,
                $name,
                $parameters
            );
        }

        if (!$apiResult->isOk()) {
            $message = $apiResult->getErrorMessage();
            throw new ExpertsenderServiceApiException(
                $message,
                $apiResult->getErrorCode(),
                null,
                $name,
                $parameters
            );
        }

        return $apiResult;
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    protected function isBlackListed(string $message): bool
    {
        if(!empty($message)) {
            if ($message === self::BLACK_LIST_ERROR_MESSAGE) {
                return true;
            }

            $sop = \simplexml_load_string($message);
            if (isset($sop->ErrorMessage) && (int)$sop->ErrorMessage->Code === self::BLACK_LIST_ERROR_CODE
                && (string)$sop->ErrorMessage->Message === self::BLACK_LIST_ERROR_MESSAGE) {
                return true;
            }
        }

        return false;
    }
}
