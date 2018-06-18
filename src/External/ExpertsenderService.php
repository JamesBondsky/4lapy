<?php

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
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
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LinguaLeo\ExpertSender\Entities\Property;
use LinguaLeo\ExpertSender\Entities\Receiver;
use LinguaLeo\ExpertSender\Entities\Snippet;
use LinguaLeo\ExpertSender\ExpertSender;
use LinguaLeo\ExpertSender\ExpertSenderException;
use LinguaLeo\ExpertSender\Request\AddUserToList;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ExpertsenderService
 *
 * @todo    переписать нахер
 *
 * @package FourPaws\External
 */
class ExpertsenderService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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

    public function __construct()
    {
        $client = new Client();
        $this->guzzleClient = $client;

        [$url, $key] = \array_values(Application::getInstance()->getContainer()->getParameter('expertsender'));
        $this->key = $key;
        $this->url = $url;
        $this->client = new ExpertSender($url, $key, $client);

        $this->setLogger(LoggerFactory::create('expertsender'));
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
                $apiResult = $this->client->addUserToList($addUserToList);

                if ($apiResult->isOk()) {
                    return true;
                }
                throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
            } catch (SystemException|GuzzleException|Exception $e) {
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
     */
    public function sendChangePasswordByProfile(User $user): bool
    {
        if($user->hasEmail()) {
            try {
                $receiver = new Receiver($user->getEmail());
                $apiResult = $this->client->sendSystemTransactional(self::CHANGE_PASSWORD_LIST_ID, $receiver);
                if ($apiResult->isOk()) {
                    return true;
                }
                throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
            } catch (ExpertSenderException|GuzzleException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * @param User   $user
     * @param string $backUrl
     *
     * @return bool
     * @throws ExpertsenderServiceException
     */
    public function sendForgotPassword(User $user, string $backUrl = ''): bool
    {
        if ($user->hasEmail()) {
            try {
                /** хеш строка для подтверждения мыла */
                /** @var ConfirmCodeService $confirmService */
                $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $confirmService::setGeneratedHash($user->getEmail(), 'email_forgot');
                $receiver = new Receiver($user->getEmail());
                $backUrlText = !empty($backUrl) ? '&backurl=' . $backUrl . '&user_id=' . $user->getId() : '';
                $snippets = [
                    new Snippet('user_name', $user->getName() ?: $user->getFullName(), true),
                    new Snippet('link',
                        (new FullHrefDecorator('/personal/forgot-password/?hash=' . $confirmService::getGeneratedCode('email_forgot') . '&email=' . $user->getEmail() . $backUrlText))->getFullPublicPath(),
                        true),
                ];
                $apiResult = $this->client->sendSystemTransactional(self::FORGOT_PASSWORD_LIST_ID, $receiver, $snippets);
                if ($apiResult->isOk()) {
                    return true;
                }
                throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
            } catch (ExpertSenderException|GuzzleException|ApplicationCreateException|Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return false;
    }

    /**
     * @param User $oldUser
     * @param User $curUser
     *
     * @return bool
     * @throws ExpertsenderServiceException
     */
    public function sendChangeEmail(User $oldUser, User $curUser): bool
    {
        $continue = true;
        $expertSenderId = 0;
        $hasExpertSenderId = false;
        $hasNewEmailInSender = false;
        /** проверяем наличие новой почты в сендере */
        if($curUser->hasEmail()) {
            try {
                $userIdResult = $this->client->getUserId($curUser->getEmail());
                if ($userIdResult->isOk()) {
                    $hasNewEmailInSender = true;
                }
            } catch (GuzzleException | Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }

        if ($oldUser->hasEmail()) {

            $continue = false;
            /** отправка почты на старый email */
            try {
                $receiver = new Receiver($oldUser->getEmail());
                $apiResult = $this->client->sendSystemTransactional(self::CHANGE_EMAIL_LIST_ID, $receiver);
                if ($apiResult->isOk()) {
                    $continue = true;
                }

                if(!$hasNewEmailInSender) {
                    /** получение id подписчика по старому email */
                    $userIdResult = $this->client->getUserId($oldUser->getEmail());
                    if ($userIdResult->isOk()) {
                        $expertSenderId = $userIdResult->getId();
                        if (!empty($expertSenderId)) {
                            $hasExpertSenderId = true;
                        }
                    }
                }
            } catch (GuzzleException | Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        if ($continue && $curUser->hasEmail()) {
            try {
                if(!$hasNewEmailInSender) {
                    $continue = false;
                    if ($hasExpertSenderId) {
                        $addUserToList = new AddUserToList();
                        $addUserToList->setForce(true);
                        $addUserToList->setMode(static::MAIN_LIST_MODE);
                        $addUserToList->setTrackingCode('change_email');
                        $addUserToList->setListId(static::MAIN_LIST_ID);
                        $addUserToList->setEmail($curUser->getEmail());
                        $addUserToList->setId($expertSenderId);

                        $addUserToList->setName($curUser->getName());
                        $addUserToList->setLastName($curUser->getLastName());
                        /** ip юзверя */
                        $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_IP_ID, 'string',
                            BitrixApplication::getInstance()->getContext()->getServer()->get('REMOTE_ADDR')));

                        $apiResult = $this->client->addUserToList($addUserToList);
                        if ($apiResult->isOk()) {
                            $continue = true;
                        } else {
                            throw new ExpertsenderServiceException($apiResult->getErrorMessage(),
                                $apiResult->getErrorCode());
                        }
                    } else {
                        /** если нет старой почты или не нашли на сайте регистрируем в сендере */
                        if ($this->sendEmailAfterRegister($curUser, ['isReg' => false, 'type' => 'email_change_email'])) {
                            $continue = true;
                        }
                    }
                }

                if ($continue) {
                    /** отправка почты на новый email, отправляем именно при смене и при регистрации */
                    $receiver = new Receiver($curUser->getEmail());
                    $apiResult = $this->client->sendSystemTransactional(self::CHANGE_EMAIL_TO_NEW_EMAIL_LIST, $receiver);
                    if ($apiResult->isOk()) {
                        return true;
                    }
                    throw new ExpertsenderServiceException($apiResult->getErrorMessage(),
                        $apiResult->getErrorCode());
                }
            } catch (GuzzleException|Exception $e) {
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
     */
    public function changeUserData(User $curUser): bool
    {
        $continue = true;
        $expertSenderId = 0;
        $hasExpertSenderId = false;
        if ($curUser->hasEmail()) {
            try {
                /** получение id подписчика */
                $userIdResult = $this->client->getUserId($curUser->getEmail());
                if ($userIdResult->isOk()) {
                    $expertSenderId = $userIdResult->getId();
                    if(!empty($expertSenderId)) {
                        $hasExpertSenderId = true;
                    }
                }
            } catch (GuzzleException | Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        if ($continue && $hasExpertSenderId && $curUser->hasEmail()) {
            try {
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

                $apiResult = $this->client->addUserToList($addUserToList);
                if ($apiResult->isOk()) {
                    return true;
                }

                throw new ExpertsenderServiceException($apiResult->getErrorMessage(),
                    $apiResult->getErrorCode());
            } catch (GuzzleException|Exception $e) {
                $e->getMessage();
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ExpertsenderServiceException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function sendEmailSubscribeNews(User $user): bool
    {
        if ($user->hasEmail()) {
            try {
                $expertSenderId = 0;
                $hasExpertSenderId = false;
                $userIdResult = $this->client->getUserId($user->getEmail());
                if ($userIdResult->isOk()) {
                    $expertSenderId = $userIdResult->getId();
                    if(!empty($expertSenderId)){
                        $hasExpertSenderId = true;
                    }
                }

                if ($hasExpertSenderId) {
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


                    $apiResult = $this->client->addUserToList($addUserToList);
                    if ($apiResult->isOk()) {
                        return true;
                    }
                    throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
                }

                /** если не нашли id по почте регистрируем в сендере */
                return $this->sendEmailAfterRegister($user,
                    ['isReg' => false, 'type' => 'email_subscribe', 'subscribe' => true]);
            } catch (GuzzleException|Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ExpertsenderServiceException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function sendEmailUnSubscribeNews(User $user): bool
    {
        if ($user->hasEmail()) {
            try {
                $expertSenderId = 0;
                $hasExpertSenderId = false;
                $userIdResult = $this->client->getUserId($user->getEmail());
                if ($userIdResult->isOk()) {
                    $expertSenderId = $userIdResult->getId();
                    if(!empty($expertSenderId)){
                        $hasExpertSenderId = true;
                    }
                }

                if ($hasExpertSenderId) {
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

                    $apiResult = $this->client->addUserToList($addUserToList);
                    if ($apiResult->isOk()) {
                        return true;
                    }

                    throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
                }
                return true;
            } catch (GuzzleException|Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
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
     *
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws ApplicationCreateException
     * @throws ExpertsenderServiceException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @return int
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
            'EMAIL'
        ]);

        /**
         * Не отправляем письма для заказов в 1 клик
         */
        if ($properties['COM_WAY'] === OrderPropertyService::COMMUNICATION_ONE_CLICK) {
            return 0;
        }


        if (!$email = $properties['EMAIL']) {
            throw new ExpertsenderServiceException('order email is empty');
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

        try {
            $apiResult = $this->client->sendSystemTransactional($transactionId, new Receiver($email), $snippets);
            if (!$apiResult->isOk()) {
                throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
            }
            return $transactionId;
        } catch (GuzzleException|Exception $e) {
            throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param Order $order
     *
     * @return int
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ExpertsenderServiceException
     * @throws SystemException
     * @throws ObjectPropertyException
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

        if (!$email = $orderService->getOrderPropertyByCode($order, 'EMAIL')->getValue()) {
            throw new ExpertsenderServiceException('order email is empty');
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

        try {
            $apiResult = $this->client->sendSystemTransactional($transactionId, new Receiver($email), $snippets);
            if (!$apiResult->isOk()) {
                throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
            }

            return $transactionId;
        } catch (GuzzleException|Exception $e) {
            throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Basket $basket
     *
     * @param int    $type
     * type = 1 - отправка после закрытия спустя 3 часа
     * type = 2 - отправка спустя 3 дня если не было измениней после типа 1
     *
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ExpertsenderServiceException
     */
    public function sendForgotBasket(Basket $basket, int $type):bool
    {
        switch ($type){
            case static::FORGOT_BASKET_TO_CLOSE_SITE:
                $transactionId = self::FORGOT_BASKET_LIST_ID;
                break;
            case static::FORGOT_BASKET_AFTER_TIME:
                $transactionId = self::FORGOT_BASKET2_LIST_ID;
                break;
            default:
                $transactionId = 0;
        }
        if($transactionId === 0){
            throw new ExpertsenderServiceException('unknown forgotBasket time');
        }
        $snippets = [];

        $container = Application::getInstance()->getContainer();

        $userService = $container->get(CurrentUserProviderInterface::class);
        $user = $userService->getUserRepository()->find(FuserTable::getUserById($basket->getFUserId()));
        if($user === null){
            throw new ExpertsenderServiceException('user not found');
        }
        if (!$user->hasEmail()) {
            throw new ExpertsenderServiceException('email is empty');
        }

        /** @var BasketService $orderService */
        $basketService = $container->get(BasketService::class);

        $snippets[] = new Snippet('user_name', $user->getName() ?: $user->getFullName());
        $snippets[] = new Snippet('total_bonuses', (int)$basketService->getBasketBonus($user));

        $items = $this->getAltProductsItemsByBasket($basket);
        $items = '<Products>' . implode('', $items) . '</Products>';
        $snippets[] = new Snippet('alt_products', $items, true);

        try {
            $apiResult = $this->client->sendSystemTransactional(
                $transactionId,
                new Receiver($user->getEmail()),
                $snippets
            );
            if (!$apiResult->isOk()) {
                throw new ExpertsenderServiceException(
                    $apiResult->getErrorMessage(),
                    $apiResult->getErrorCode()
                );
            }
        } catch (GuzzleException|Exception $exception) {
            throw new ExpertsenderServiceException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
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
     *
     * @return array
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \InvalidArgumentException
     * @throws ApplicationCreateException
     * @throws ExpertsenderServiceException
     */
    protected function getAltProductsItems(Order $order): array
    {
        $items = [];
        try {
            /** @var OrderService $orderService */
            $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
            $offers = $orderService->getOrderProducts($order);
            $basket = $order->getBasket();
            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem) {
                $currentOffer = null;
                /** @var Offer $offer */
                foreach ($offers as $offer) {
                    if ($offer->getId() === (int)$basketItem->getProductId()) {
                        $currentOffer = $offer;
                    }
                }
                if (!$currentOffer) {
                    throw new NotFoundException(sprintf('Не найден товар %s', $basketItem->getProductId()));
                }
                $item = '';
                $item .= '<Product>';
                $item .= '<Name>' . $basketItem->getField('NAME') . '</Name>';
                $item .= '<PicUrl>' . new FullHrefDecorator((string)$offer->getImages()->first()) . '</PicUrl>';
                $item .= '<Link>' . new FullHrefDecorator($offer->getDetailPageUrl()) . '</Link>';
                $item .= '<Price1>' . $basketItem->getBasePrice() . '</Price1>';
                $item .= '<Price2>' . $basketItem->getPrice() . '</Price2>';
                $item .= '<Amount>' . $basketItem->getQuantity() . '</Amount>';
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
     *
     * @return int
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws Exception
     * @throws ExpertsenderServiceException
     * @throws ObjectPropertyException
     * @throws SystemException
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
        if ($email === '') {
            throw new ExpertsenderServiceException('order email is empty');
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        $order = $personalOrder->getBitrixOrder();

        $snippets[] = new Snippet('user_name', $personalOrder->getPropValue('NAME'));
        $snippets[] = new Snippet('delivery_address', $orderService->getOrderDeliveryAddress($order));
        $snippets[] = new Snippet('delivery_date', $orderSubscribe->getDateStartFormatted());
        $snippets[] = new Snippet('delivery_period', $orderSubscribe->getDeliveryTimeFormattedRu());
        $snippets[] = new Snippet('tel_number', PhoneHelper::formatPhone($personalOrder->getPropValue('PHONE')));
        $snippets[] = new Snippet('total_bonuses', (int)$orderService->getOrderBonusSum($order));

        $items = $this->getAltProductsItems($order);
        $items = '<Products>' . implode('', $items) . '</Products>';
        $snippets[] = new Snippet('alt_products', $items, true);

        $this->logger->info(
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

        try {
            $apiResult = $this->client->sendSystemTransactional(
                $transactionId,
                new Receiver($email),
                $snippets
            );
            if (!$apiResult->isOk()) {
                throw new ExpertsenderServiceException(
                    $apiResult->getErrorMessage(),
                    $apiResult->getErrorCode()
                );
            }
        } catch (GuzzleException|Exception $exception) {
            throw new ExpertsenderServiceException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

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
     * @throws ExpertsenderServiceException
     * @throws ObjectPropertyException
     * @throws SystemException
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
        if ($email === '') {
            throw new ExpertsenderServiceException('order email is empty');
        }

        $snippets[] = new Snippet('user_name', $properties['NAME']);
        $snippets[] = new Snippet('delivery_address', $orderService->getOrderDeliveryAddress($order));
        $snippets[] = new Snippet('delivery_date', $properties['DELIVERY_DATE']);
        $snippets[] = new Snippet('delivery_time', $properties['DELIVERY_INTERVAL']);
        $snippets[] = new Snippet('tel_number', $properties['PHONE'] !== '' ? PhoneHelper::formatPhone($properties['PHONE']) : '');
        $snippets[] = new Snippet('total_bonuses', (int)$orderService->getOrderBonusSum($order));

        $items = $this->getAltProductsItems($order);
        $items = '<Products>' . implode('', $items) . '</Products>';
        $snippets[] = new Snippet('alt_products', $items, true);

        $this->logger->info(
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

        try {
            $apiResult = $this->client->sendSystemTransactional(
                $transactionId,
                new Receiver($email),
                $snippets
            );
            if (!$apiResult->isOk()) {
                throw new ExpertsenderServiceException(
                    $apiResult->getErrorMessage(),
                    $apiResult->getErrorCode()
                );
            }
        } catch (GuzzleException|Exception $exception) {
            throw new ExpertsenderServiceException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return $transactionId;
    }
}
