<?php

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\External\Exception\ExpertsenderBasketEmptyException;
use FourPaws\External\Exception\ExpertsenderEmptyEmailException;
use FourPaws\External\Exception\ExpertsenderNotAllowedException;
use FourPaws\External\Exception\ExpertSenderOfferNotFoundException;
use FourPaws\External\Exception\ExpertsenderServiceApiException;
use FourPaws\External\Exception\ExpertsenderServiceBlackListException;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\External\ExpertSender\Dto\ForgotBasket;
use FourPaws\External\ExpertSender\Dto\PetBirthDay;
use FourPaws\Helpers\BxCollection;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\Pet;
use FourPaws\PersonalBundle\Models\PetCongratulationsNotify;
use FourPaws\PersonalBundle\Service\OrderSubscribeHistoryService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Dto\Fiscalization\Item;
use FourPaws\SaleBundle\Repository\Table\AnimalShelterTable;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\PaymentService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundException as UserNotFoundException;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserSearchInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use JMS\Serializer\SerializerInterface;
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
use Symfony\Component\HttpFoundation\Response;
use FourPaws\AppBundle\AjaxController\LandingController;

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

    public const FORGOT_BASKET_LIST_ID = 7765;
    public const FORGOT_BASKET2_LIST_ID = 7767;
    public const CHANGE_EMAIL_LIST_ID = 7766;
    public const CHANGE_EMAIL_TO_NEW_EMAIL_LIST = 7768;
    public const CHANGE_EMAIL_CODE_LIST_ID = 8009;
    public const SUBSCRIBE_EMAIL_UNDER_3_WEEK_LIST_ID = 7769;
    public const SUBSCRIBE_EMAIL_UNDER_3_DAYS_LIST_ID = 7773;
    public const SUBSCRIBE_CANCEL = 9413;

    public const NEW_ORDER_PAY_LIST_ID = 7774;
    public const NEW_ORDER_NOT_PAY_LIST_ID = 7775;
    public const NEW_ORDER_NOT_REG_PAY_LIST_ID = 7776;
    public const NEW_ORDER_NOT_REG_NOT_PAY_LIST_ID = 7777;

    public const NEW_ORDER_PAY_LIST_ID_ROYAL_CANIN = 9178;
    public const NEW_ORDER_NOT_PAY_LIST_ID_ROYAL_CANIN = 9179;
    public const NEW_ORDER_NOT_REG_PAY_LIST_ID_ROYAL_CANIN = 9180;
    public const NEW_ORDER_NOT_REG_NOT_PAY_LIST_ID_ROYAL_CANIN = 9181;
    public const NEW_ORDER_SUBSCRIBE = 9404;

    public const COMPLETE_ORDER_LIST_ID = 7778;
    public const FORGOT_PASSWORD_LIST_ID = 7779;
    public const CHANGE_PASSWORD_LIST_ID = 7780;
    public const NEW_CHECK_REG_LIST_ID = 8906;
    public const CHANGE_BONUS_CARD = 8026;
    public const PIGGY_BANK_SEND_EMAIL = 9006;
    public const PERSONAL_OFFER_COUPON_SEND_EMAIL = 9607;
    public const GRANDIN_NEW_CHECK_REG_LIST_ID = 8906;
    public const ROYAL_CANIN_NEW_CHECK_REG_LIST_ID = 9195;
    public const FESTIVAL_NEW_USER_REG_LIST_ID = 9233;
    public const MEALFEEL_NEW_CHECK_REG_LIST_ID = 8919;
    public const PERSONAL_OFFER_COUPON_START_SEND_EMAIL = 9607;
    public const PERSONAL_OFFER_COUPON_END_SEND_EMAIL = 9608;
    public const COMPLETE_ORDER_DOBROLAP_LIST_ID = 9609;
    /**
     * BirthDay mail ids
     */
    public const CATS_BIRTH_DAY = 8420;
    public const DOGS_BIRTH_DAY = 8421;
    public const OTHER_BIRTH_DAY = 8422;
    
    /**
     * Flagman
     */
    public const GROOMING_SEND_EMAIL = 10130;
    public const TRAINING_SEND_EMAIL = 10135;
    public const LECTION_SEND_EMAIL = 10159;

    public const BLACK_LIST_ERROR_CODE = 400;
    public const BLACK_LIST_ERROR_MESSAGE = 'Subscriber is blacklisted.';

    public const CHANGE_PASSWORD = 9641;

    /**
     * @var SmsService
     */
    protected $smsService;
    /**
     * @var UserSearchInterface
     */
    protected $userService;

    /**
     * ExpertsenderService constructor.
     * @param UserSearchInterface $userSearch
     * @param SmsService $smsService
     */
    public function __construct(UserSearchInterface $userSearch, SmsService $smsService)
    {
        $client = new Client();
        $this->guzzleClient = $client;

        [$url, $key] = \array_values(Application::getInstance()->getContainer()->getParameter('expertsender'));
        $this->key = $key;
        $this->url = $url;
        $this->client = new ExpertSender($url, $key, $client);

        $this->smsService = $smsService;
        $this->userService = $userSearch;
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
     * @param string|null $hash
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
    public function sendForgotPassword(User $user, string $backUrl = '', ?string $hash = ''): bool
    {
        if ($user->hasEmail()) {
            try {
                $transactionId = self::FORGOT_PASSWORD_LIST_ID;

                /** хеш строка для подтверждения мыла */
                /** @var ConfirmCodeService $confirmService */
                $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $email = $user->getEmail();
                $userId = $user->getId();
                $confirmService::setGeneratedHash($email, 'email_forgot', 0, $hash);
                $backUrlText = !empty($backUrl) ? '&backurl=' . $backUrl . '&user_id=' . $userId : '';
                $snippets = [
                    new Snippet('user_name', $user->getName() ?: $user->getFullName(), true),
                    new Snippet('link',
                        (new FullHrefDecorator('/personal/forgot-password/?hash=' . $confirmService::getGeneratedCode('email_forgot') . '&email=' . $email . $backUrlText . '&emailHash=' . $hash))->getFullPublicPath(),
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
            } catch (ExpertsenderServiceApiException|ExpertsenderServiceException $e) {
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

                        $addUserToList->setFirstName($curUser->getName());
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
            $addUserToList->setFirstName($curUser->getName());
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
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     * @throws ServiceNotFoundException
     * @throws SystemException
     */
    public function sendEmailSubscribeNews(User $user): bool
    {
        if ($user->hasEmail()) {
            $expertSenderId = 0;
            try {
                $expertSenderId = $this->getUserId($user->getEmail())->getId();
            } catch (ExpertsenderServiceApiException $e) {
            } catch (ExpertsenderServiceException $e) {
                if ($e->getCode() !== Response::HTTP_BAD_REQUEST) {
                    throw $e;
                }
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
            'DELIVERY_INTERVAL',
            'PHONE',
            'USER_REGISTERED',
            'COM_WAY',
            'EMAIL',
            'SUBSCRIBE_ID',
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

        if ($deliveryInterval = IntervalService::validateDeliveryInterval($properties['DELIVERY_INTERVAL'])) {
            $snippets[] = new Snippet('delivery_interval', $deliveryInterval);
        }

        $isOnlinePayment = $orderService->isOnlinePayment($order);
        $royalCaninAction = $orderService->checkRoyalCaninAction($order);
        if ($properties['USER_REGISTERED'] === BitrixUtils::BX_BOOL_TRUE) {
            // зарегистрированный пользователь
            if ($isOnlinePayment) {
                // онлайн-оплата
                if ($orderService->getOrderDeliveryCode($order) === DeliveryService::DOBROLAP_DELIVERY_CODE) {
                    $transactionId = self::COMPLETE_ORDER_DOBROLAP_LIST_ID;
                    $shelterBarcode = $this->getPropertyValueByCode($order, 'DOBROLAP_SHELTER');
                    $shelter = AnimalShelterTable::getByBarcode($shelterBarcode);
                    if ($shelter) {
                        $snippets[] = new Snippet('delivery_address', $shelter['name'] . ', ' . $shelter['city']);
                    }
                } elseif (!$royalCaninAction) {
                    $transactionId = self::NEW_ORDER_PAY_LIST_ID;
                } else {
                    $transactionId = self::NEW_ORDER_PAY_LIST_ID_ROYAL_CANIN;
                }
            } else {
                // оплата при получении
                if (!$royalCaninAction) {
                    $transactionId = self::NEW_ORDER_NOT_PAY_LIST_ID;
                } else {
                    $transactionId = self::NEW_ORDER_NOT_PAY_LIST_ID_ROYAL_CANIN;
                }
            }
        } else {
            // незарегистрированный пользователь
            /* @todo вынести из сессии? */
            $snippets[] = new Snippet('login', $_SESSION['NEW_USER']['LOGIN']);
            $snippets[] = new Snippet('password', $_SESSION['NEW_USER']['PASSWORD']);
            if ($isOnlinePayment) {
                // онлайн-оплата
                if (!$royalCaninAction) {
                    $transactionId = self::NEW_ORDER_NOT_REG_PAY_LIST_ID;
                } else {
                    $transactionId = self::NEW_ORDER_NOT_REG_PAY_LIST_ID_ROYAL_CANIN;
                }
                return false;
            } else {
                // оплата при получении
                if (!$royalCaninAction) {
                    $transactionId = self::NEW_ORDER_NOT_REG_NOT_PAY_LIST_ID;
                } else {
                    $transactionId = self::NEW_ORDER_NOT_REG_NOT_PAY_LIST_ID_ROYAL_CANIN;
                }
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
            new Snippet(
                'order_feedback_link',
                (new FullHrefDecorator($orderService->getOrderFeedbackLink($order)))->__toString()
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
     * @param ForgotBasket $forgotBasket
     * @return bool
     * @throws ArgumentNullException
     * @throws ExpertSenderException
     * @throws ExpertSenderOfferNotFoundException
     * @throws ExpertsenderBasketEmptyException
     * @throws ExpertsenderEmptyEmailException
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceBlackListException
     * @throws ExpertsenderServiceException
     * @throws RuntimeException
     * @throws \InvalidArgumentException
     */
    public function sendForgotBasket(ForgotBasket $forgotBasket): bool
    {
        if (!$forgotBasket->getUserEmail()) {
            throw new ExpertsenderEmptyEmailException('Email is empty');
        }

        switch ($forgotBasket->getMessageType()) {
            case static::FORGOT_BASKET_TO_CLOSE_SITE:
                $transactionId = self::FORGOT_BASKET_LIST_ID;
                break;
            case static::FORGOT_BASKET_AFTER_TIME:
                $transactionId = self::FORGOT_BASKET2_LIST_ID;
                break;
            default:
                throw new ExpertsenderServiceException('Unknown forgotBasket message type');
        }
        $snippets = [
            new Snippet('user_name', $forgotBasket->getUserName()),
            new Snippet('total_bonuses', $forgotBasket->getBonusCount())
        ];

        if (!$items = $this->getAltProductsItemsByBasket($forgotBasket->getBasket())) {
            throw new ExpertsenderBasketEmptyException('basket is empty');
        }

        $items = '<Products>' . implode('', $items) . '</Products>';
        $snippets[] = new Snippet('alt_products', $items, true);

        try {
            $this->sendSystemTransactional($transactionId, $forgotBasket->getUserEmail(), $snippets);
        } catch (ExpertsenderServiceApiException | ExpertsenderServiceException $e) {
            $message = $e->getMessage();
            /** чекаем на черный список */
            if ($this->isBlackListed($message)) {
                throw new ExpertsenderServiceBlackListException($message, $e->getCode(), $e, $e->getMethod(),
                    $e->getParameters());
            }
            throw $e;
        }

        return true;
    }

    /**
     * @param User $user
     * @return bool
     * @throws ExpertsenderServiceException
     */
    public function sendChangeBonusCardFromMobileApp(User $user)
    {
        if ($user->hasEmail()) {
            try {
                $transactionId = static::CHANGE_BONUS_CARD;
                $email = $user->getEmail();

                /** @var ConfirmCodeService $confirmService */
                $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $confirmService::setGeneratedCode($email, 'email_change_bonus_card');
                $snippets = [
                    new Snippet('code', $confirmService::getGeneratedCode('email_change_bonus_card'))
                ];
                unset($confirmService);
                $this->sendSystemTransactional($transactionId, $email, $snippets);
                return true;
            } catch (Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return false;
    }

    /**
     * @param string $email
     * @param string $code
     *
     * @return bool
     * @throws ExpertSenderException
     * @throws ExpertsenderServiceException
     */
    public function sendConfirmEmail(string $email, string $code): bool
    {
        $transactionIdCode = self::CHANGE_EMAIL_CODE_LIST_ID;

        try {
            $snippets = [];
            $snippets[] = new Snippet('text', 'Код подтверждения смены адреса электронной почты. Если вы не вносили этих изменений, свяжитесь с нами по телефону +7 (800) 770-00-22');
            $snippets[] = new Snippet('code', $code);
            $this->sendSystemTransactional($transactionIdCode, $email, $snippets);
            return true;
        } catch (ExpertsenderServiceApiException $e) {
        }

        return false;
    }

    /**
     * @param Basket $basket
     *
     * @return array
     * @throws ArgumentNullException
     * @throws RuntimeException
     * @throws \InvalidArgumentException
     * @throws ExpertSenderOfferNotFoundException
     */
    protected function getAltProductsItemsByBasket(?Basket $basket): array
    {
        $items = [];
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $currentOffer = OfferQuery::getById((int)$basketItem->getProductId());
            if ($currentOffer === null) {
                throw new ExpertSenderOfferNotFoundException(sprintf('Не найден товар %s', $basketItem->getProductId()));
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
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ExpertSenderOfferNotFoundException
     * @throws ExpertsenderServiceException
     * @throws ExpertSenderOfferNotFoundException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \Bitrix\Main\IO\InvalidPathException
     * @throws \InvalidArgumentException
     * @throws \LogicException
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

                /** исключаем из письма офферы-доставки */
                if (mb_strpos($basketItem->getCode(), 'DELIVERY') !== false) {
                    continue;
                }

                /** исключаем из письма офферы-марки (акция копилка-собиралка) */
                if (in_array($basketItem->getXmlId(), PiggyBankService::getMarkXmlIds())) {
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
                    throw new ExpertSenderOfferNotFoundException(sprintf('Не найден товар %s', $basketItem->getCode()));
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
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\NotFoundException
     */
    public function sendOrderSubscribedEmail(OrderSubscribe $orderSubscribe): int
    {
        $transactionId = self::NEW_ORDER_SUBSCRIBE;
        $snippets = [];

        $personalOrder = $orderSubscribe->getOrder();
        $email = $personalOrder->getPropValue('EMAIL');
        if (empty($email)) {
            throw new ExpertsenderEmptyEmailException('order email is empty');
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        /** @var OrderSubscribeHistoryService $orderSubscribeHistoryService */
        $orderSubscribeHistoryService = Application::getInstance()->getContainer()->get('order_subscribe_history.service');
        $order = $orderService->getOrderById($orderSubscribeHistoryService->getLastCreatedOrderId($orderSubscribe));

        /** @var OrderSubscribeService $orderSubscribeService */
        $orderSubscribeService = Application::getInstance()->getContainer()->get('order_subscribe.service');
        $frequency = $orderSubscribe->getFrequency();
        $frequencyList = $orderSubscribeService->getFrequencies();
        $curFrequency = current(array_filter($frequencyList, function($item) use ($frequency) { return $item['ID'] == $frequency; }));
        $saleBonus = $orderSubscribeService->countBasketPriceDiff($order->getBasket());

        $snippets[] = new Snippet('user_name', htmlspecialcharsbx($personalOrder->getPropValue('NAME')));
        $snippets[] = new Snippet('delivery_address', htmlspecialcharsbx($orderService->getOrderDeliveryAddress($order)));
        $snippets[] = new Snippet('delivery_date', htmlspecialcharsbx($orderSubscribeService->getPreviousDate($orderSubscribe)->format('d.m.Y')));
        $snippets[] = new Snippet('tel_number', PhoneHelper::formatPhone($personalOrder->getPropValue('PHONE')));
        $snippets[] = new Snippet('total_bonuses', (int)$orderService->getOrderBonusSum($order));
        $snippets[] = new Snippet('delivery_cost', (float)$order->getShipmentCollection()->getPriceDelivery());
        $snippets[] = new Snippet('delivery_period', (string)$curFrequency['VALUE']);
        $snippets[] = new Snippet('next_delivery_date', $orderSubscribe->getNextDate()->format('d.m.Y'));
        $snippets[] = new Snippet('sale_bonus', abs($saleBonus));

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

        if ($deliveryInterval = IntervalService::validateDeliveryInterval($properties['DELIVERY_INTERVAL'])) {
            $snippets[] = new Snippet('delivery_interval', $deliveryInterval);
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
     * @param OrderSubscribe $orderSubscribe
     * @return int
     * @throws ApplicationCreateException
     * @throws ExpertSenderException
     * @throws ExpertsenderEmptyEmailException
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\PersonalBundle\Exception\NotFoundException
     */
    public function sendOrderSubscribeCancelEmail(OrderSubscribe $orderSubscribe): int
    {
        $transactionId = self::SUBSCRIBE_CANCEL;
        $snippets = [];
        $personalOrder = $orderSubscribe->getOrder();

        $email = $personalOrder->getPropValue('EMAIL');
        if (empty($email)) {
            throw new ExpertsenderEmptyEmailException('order email is empty');
        }

        $snippets[] = new Snippet('user_name', htmlspecialcharsbx($personalOrder->getPropValue('NAME')));
        $snippets[] = new Snippet('order_number', htmlspecialcharsbx($personalOrder->getAccountNumber()));
        $snippets[] = new Snippet('date', htmlspecialcharsbx($orderSubscribe->getDateCreate()));

        $this->log()->info(
            __FUNCTION__,
            [
                'email' => $email,
                'transactionId' => $transactionId,
                'orderId' => $orderSubscribe->getOrderId(),
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
     * @param PetCongratulationsNotify $pet
     * @return int
     * @throws ExpertSenderException
     * @throws ExpertsenderEmptyEmailException
     * @throws ExpertsenderNotAllowedException
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     */
    public function sendBirthDayCongratulationsEmail(PetCongratulationsNotify $pet)
    {
        $email = $pet->getOwnerEmail();
        if (empty($email)) {
            throw new ExpertsenderEmptyEmailException('order email is empty');
        }

        if($pet->isDog()) {
            $transactionId = self::DOGS_BIRTH_DAY;
        }
        elseif($pet->isCat()) {
            $transactionId = self::CATS_BIRTH_DAY;
        }
        else {
            $transactionId = self::OTHER_BIRTH_DAY;
        }

        $snippets[] = new Snippet('user_name', htmlspecialcharsbx($pet->getOwnerName()));
        $snippets[] = new Snippet('user_email', htmlspecialcharsbx($email));
        $snippets[] = new Snippet('pet_name', htmlspecialcharsbx($pet->getPetName()));
        $snippets[] = new Snippet('pet_id', htmlspecialcharsbx($pet->getPetId()));

        $this->log()->info(
            __FUNCTION__,
            [
                'email' => $email,
                'transactionId' => $transactionId,
                'petId' => $pet->getPetId(),
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
        $this->sendSystemTransactional($transactionId, 'v.salshin@articul.ru', $snippets);
        return $transactionId;
    }

    public function sendNewPassword(string $password, User $user, ?string $link = '', ?string $shortLink = '')
    {
        $snippets[] = new Snippet('user_name', htmlspecialcharsbx($user->getLogin()));
        $snippets[] = new Snippet('pass', $password);
        if ($link) {
            $snippets[] = new Snippet('link', $link, true);
        }

        $email = $user->getEmail();

        if ($email) {
            $this->sendSystemTransactional(self::CHANGE_PASSWORD, $email, $snippets);
        } else {
            $phone = $user->getPersonalPhone();
            if (!$phone) {
                $phone = $user->getLogin();
            }
            $smsText = 'Вы давно не меняли пароль, ваш пароль изменен автоматически: ' . $password . "\nДля восстановления пароля перейдите по ссылке:";

            if ($shortLink) {
                $smsText .= ' ' . $shortLink;
            }

            $this->smsService->sendSmsImmediate($smsText, $phone);
        }
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
     * @param int    $transactionId
     * @param string $email
     * @param array  $snippets
     *
     * @return ApiResult
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     * @throws ExpertSenderException
     */
    protected function sendTransactional(int $transactionId, string $email, array $snippets = []): ApiResult
    {
        return $this->sendRequest(
            'sendTransactional',
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

    /**
     * @param array $params
     *
     * @return bool
     * @throws ExpertSenderException
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     */
    public function sendAfterCheckReg(array $params): bool
    {
        $email = $params['userEmail'];
        $userId = $params['userId'];
        $landingType = $params['landingType'];

        if ($email) {
            switch ($landingType) {
                case LandingController::$grandinLanding:
                    $transactionId = self::GRANDIN_NEW_CHECK_REG_LIST_ID;
                    break;
                case LandingController::$royalCaninLanding:
                    $transactionId = self::ROYAL_CANIN_NEW_CHECK_REG_LIST_ID;
                    break;
                case LandingController::$mealfeelLanding:
                    $transactionId = self::MEALFEEL_NEW_CHECK_REG_LIST_ID;
                    break;
                default:
                    $transactionId = self::GRANDIN_NEW_CHECK_REG_LIST_ID;
            }

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
     * @param array $params
     *
     * @return bool
     * @throws ExpertSenderException
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     */
    public function sendAfterFestivalUserReg(array $params): bool
    {
        $email = $params['userEmail'];
        $coupon = $params['coupon'];
        $firstname = $params['firstname'];
        $lastname = $params['lastname'];
        $base64 = $params['url_img'];

        if ($email) {
            $transactionId = self::FESTIVAL_NEW_USER_REG_LIST_ID;

            $this->log()->info(
                __FUNCTION__,
                [
                    'email' => $email,
                    'transactionId' => $transactionId,
                    'coupon' => $coupon,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'url_img' => $base64,
                ]
            );

            $snippets = [];
            $snippets[] = new Snippet('coupon', htmlspecialcharsbx($coupon));
            $snippets[] = new Snippet('firstname', htmlspecialcharsbx($firstname));
            $snippets[] = new Snippet('lastname', htmlspecialcharsbx($lastname));
            $snippets[] = new Snippet('url_img', $base64);

            $senderApiResult = $this->sendSystemTransactional($transactionId, $email, $snippets);
            if (!$senderApiResult->isOk()) {
                throw new ExpertSenderException(__METHOD__ . 'Не удалось отправить письмо: Ошибка #' . $senderApiResult->getErrorCode() . '. ' .  $senderApiResult->getErrorMessage() . '. $params: ' . print_r($params, true));
            }
            return true;
        } else {
            throw new ExpertsenderEmptyEmailException(__METHOD__ . 'Не удалось отправить письмо: не указан email. $params: ' . print_r($params, true));
        }

        return false;
    }

    /**
     * @param int $userId
     * @param string $fullname
     * @param string $email
     * @param string $coupon
     * @param string $base64
     * @param string $discount
     * @return bool
     * @throws ExpertSenderException
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     */
    public function sendPiggyBankEmail($userId, $fullname, $email, $coupon, $base64, $discount): bool
    {
        if ($email) {
            $transactionId = self::PIGGY_BANK_SEND_EMAIL;

            $this->log()->info(
                __FUNCTION__,
                [
                    'userId' => $userId,
                    'fullname' => $fullname,
                    'email' => $email,
                    'coupon' => $coupon,
                    'base64' => $base64,
                    'transactionId' => $transactionId,
                ]
            );

            $snippets = [];
            $snippets[] = new Snippet('sale', htmlspecialcharsbx($discount));
            $snippets[] = new Snippet('coupon', htmlspecialcharsbx($coupon));
            $snippets[] = new Snippet('url_img', $base64);

            $this->sendSystemTransactional($transactionId, $email, $snippets);
            return true;
        }

        return false;
    }

    /**
     * @param $userId
     * @param $name
     * @param $email
     * @param $coupon
     * @param $base64
     * @param $couponDescription
     * @param $couponDateActiveTo
     * @param $discountValue
     * @param int|null $customTransactionId
     * @return bool
     * @throws ExpertSenderException
     * @throws ExpertsenderServiceApiException
     * @throws ExpertsenderServiceException
     */
    public function sendPersonalOfferCouponEmail($userId, $name, $email, $coupon, $base64, $couponDescription, $couponDateActiveTo, $discountValue, ?int $customTransactionId = 0): bool
    {
        if ($email) {
            if ($customTransactionId) {
                $transactionId = $customTransactionId;
            } else {
                $transactionId = self::PERSONAL_OFFER_COUPON_SEND_EMAIL;
            }

            $this->log()->info(
                __FUNCTION__,
                [
                    'userId' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'coupon' => $coupon,
                    'text' => $couponDescription,
                    'date' => $couponDateActiveTo,
                    'url_img' => $base64,
                    'transactionId' => $transactionId,
                    'sale' => $discountValue,
                ]
            );

            $snippets = [];
            $snippets[] = new Snippet('sale', htmlspecialcharsbx($discountValue));
            $snippets[] = new Snippet('coupon', htmlspecialcharsbx($coupon));
            $snippets[] = new Snippet('date', htmlspecialcharsbx($couponDateActiveTo));
            $snippets[] = new Snippet('url_img', $base64);
            $snippets[] = new Snippet('description', htmlspecialcharsbx($couponDescription));
            $snippets[] = new Snippet('user_name', $name);
            //$snippets[] = new Snippet('text', htmlspecialcharsbx());

            $this->sendTransactional($transactionId, $email, $snippets);
            return true;
        }

        return false;
    }

    /**
     * @param Order  $order
     * @param string $code
     *
     * @return string
     */
    public function getPropertyValueByCode(Order $order, string $code): string
    {
        try {
            $propertyValue = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), $code);
        } catch (ArgumentException $e) {
        } catch (NotImplementedException $e) {
        }

        return (isset($propertyValue) && $propertyValue) ? ($propertyValue->getValue() ?? '') : '';
    }

    /**
     * @param CurrentUserProviderInterface $currentUser
     * @param null $newPetId
     * @param null $oldPetId
     */
    public function sendAfterPetUpdateAsync($currentUser, $newPetId = null, $oldPetId = null)
    {
        if (($newPetId === $oldPetId) || (!$newPetId && !$oldPetId) || !$currentUser) {
            return;
        }

        try {
            if (!$userId = $currentUser->getCurrentUserId()) {
                return;
            }
        } catch (NotAuthorizedException $e) {
            return;
        }

        $data = [
            'USER_ID' => $userId,
            'NEW_PET_ID' => $newPetId,
            'OLD_PET_ID' => $oldPetId,
        ];

        /** @noinspection MissingService */
        $producer = Application::getInstance()->getContainer()->get('old_sound_rabbit_mq.expert_sender_send_pets_producer');

        $serializer = Application::getInstance()->getContainer()->get(SerializerInterface::class);
        $producer->publish($serializer->serialize($data, 'json'));
    }


    /**
     * @param $userId
     * @param bool $newPetId
     * @param bool $oldPetId
     * @return bool
     * @throws ExpertsenderServiceException
     */
    public function sendAfterPetUpdate($userId = null, $newPetId = null, $oldPetId = null)
    {
        if (($newPetId === $oldPetId) || (!$newPetId && !$oldPetId) || !$userId) {
            return false;
        }

        try {
            $user = $this->userService->findOne($userId);
        } catch (\Exception $e) {
            return false;
        }

        if ($user->hasEmail()) {
            $addUserToList = new AddUserToList();
            $addUserToList->setForce(true);
            $addUserToList->setMode(static::MAIN_LIST_MODE);
            $addUserToList->setListId(static::MAIN_LIST_ID);
            $addUserToList->setEmail($user->getEmail());

            if ($newPetId) {
                $addUserToList->addProperty(new Property($newPetId, 'integer', 1));
            }

            if ($oldPetId) {
                $addUserToList->addProperty(new Property($oldPetId, 'integer', 0));
            }

            try {
                /** хеш строка для подтверждения мыла */
                /** @var ConfirmCodeService $confirmService */
                $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $confirmService::setGeneratedHash($user->getEmail());
                $addUserToList->addProperty(new Property(static::MAIN_LIST_PROP_HASH_ID, 'string', $confirmService::getGeneratedCode()));
                unset($generatedHash, $confirmService);

                $this->addUserToList($addUserToList);
                return true;
            } catch (Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return false;
    }
    
    /**
     * @param $name
     * @param $phone
     * @param $email
     * @param $animal
     * @param $breed
     * @param $service
     * @param $clinic
     * @param $date
     * @return bool
     * @throws \FourPaws\External\Exception\ExpertsenderServiceApiException
     * @throws \FourPaws\External\Exception\ExpertsenderServiceException
     * @throws \LinguaLeo\ExpertSender\ExpertSenderException
     */
    public function sendGroomingEmail($name, $phone, $email, $animal, $breed, $service, $clinic, $date, $time): bool
    {
        if ($email) {
            $transactionId = self::TRAINING_SEND_EMAIL;
            
            $snippets = [];
            $snippets[] = new Snippet('subscriber_firstname', htmlspecialcharsbx($name));
            $snippets[] = new Snippet('tel_number', htmlspecialcharsbx($phone));
            $snippets[] = new Snippet('Animal', htmlspecialcharsbx($animal));
            $snippets[] = new Snippet('Breed', htmlspecialcharsbx($breed));
            $snippets[] = new Snippet('Service', htmlspecialcharsbx($service));
            $snippets[] = new Snippet('EMAIL', htmlspecialcharsbx($email));
            $snippets[] = new Snippet('delivery_address', htmlspecialcharsbx($clinic));
            $snippets[] = new Snippet('delivery_date', htmlspecialcharsbx($date));
            $snippets[] = new Snippet('delivery_interval', htmlspecialcharsbx($time));
            
            $this->sendSystemTransactional($transactionId, $email, $snippets);
            return true;
        }
        
        return false;
    }
    
    /**
     * @param $name
     * @param $phone
     * @param $email
     * @param $animal
     * @param $breed
     * @param $service
     * @param $clinic
     * @param $date
     * @return bool
     * @throws \FourPaws\External\Exception\ExpertsenderServiceApiException
     * @throws \FourPaws\External\Exception\ExpertsenderServiceException
     * @throws \LinguaLeo\ExpertSender\ExpertSenderException
     */
    public function sendTrainingEmail($name, $phone, $email, $date, $time): bool
    {
        if ($email) {
            $transactionId = self::TRAINING_SEND_EMAIL;
            
            $snippets = [];
            $snippets[] = new Snippet('subscriber_firstname', htmlspecialcharsbx($name));
            $snippets[] = new Snippet('delivery_date', htmlspecialcharsbx($date));
            $snippets[] = new Snippet('delivery_interval', htmlspecialcharsbx($time));
            $snippets[] = new Snippet('tel_number', htmlspecialcharsbx($phone));
            $snippets[] = new Snippet('EMAIL', htmlspecialcharsbx($email));
            
            $this->sendSystemTransactional($transactionId, $email, $snippets);
            return true;
        }
        
        return false;
    }
    
    /**
     * @param $name
     * @param $phone
     * @param $email
     * @param $lectionName
     * @param $lectionDate
     * @param $lectionTime
     * @return bool
     * @throws \FourPaws\External\Exception\ExpertsenderServiceApiException
     * @throws \FourPaws\External\Exception\ExpertsenderServiceException
     * @throws \LinguaLeo\ExpertSender\ExpertSenderException
     */
    public function sendLectionEmail($name, $phone, $email, $lectionName, $lectionDate, $lectionTime): bool
    {
        if ($email) {
            $transactionId = self::LECTION_SEND_EMAIL;
            
            $snippets = [];
            $snippets[] = new Snippet('subscriber_firstname', htmlspecialcharsbx($name));
            $snippets[] = new Snippet('LECTION_NAME', htmlspecialcharsbx($lectionName));
            $snippets[] = new Snippet('tel_number', htmlspecialcharsbx($phone));
            $snippets[] = new Snippet('delivery_interval', htmlspecialcharsbx($lectionTime));
            $snippets[] = new Snippet('delivery_date', htmlspecialcharsbx($lectionDate));
            $snippets[] = new Snippet('EMAIL', htmlspecialcharsbx($email));
            
            $this->sendSystemTransactional($transactionId, $email, $snippets);
            return true;
        }
        
        return false;
    }
}
