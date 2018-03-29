<?php

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\SystemException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Exception\ExpertsenderNotAllowedException;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
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
 * @package FourPaws\External
 */
class ExpertsenderService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
            $params['subscribe'] = 0;
        }
        if (!empty($user->getEmail())) {
            $addUserToList = new AddUserToList();
            $addUserToList->setForce(true);
            $addUserToList->setMode('AddAndUpdate');
            $addUserToList->setTrackingCode('reg_form');
            $addUserToList->setListId(178);
            $addUserToList->setEmail($user->getEmail());
            $addUserToList->setFirstName($user->getName());
            $addUserToList->setLastName($user->getLastName());
            /** флаг подписки на новости */
            $addUserToList->addProperty(new Property(23, 'boolean', $params['subscribe']));
            /** флаг регистрации */
            $addUserToList->addProperty(new Property(47, 'boolean', $params['isReg']));
            try {
                /** хеш строка для подтверждения мыла */
                /** @var ConfirmCodeService $confirmService */
                $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $confirmService::setGeneratedHash($user->getEmail(), $params['type']);
                $addUserToList->addProperty(new Property(10, 'string',
                    $confirmService::getGeneratedCode($params['type'])));
                unset($generatedHash, $confirmService);
                /** ip юзверя */
                $addUserToList->addProperty(new Property(48, 'string',
                    BitrixApplication::getInstance()->getContext()->getServer()->get('REMOTE_ADDR')));
                $apiResult = $this->client->addUserToList($addUserToList);

                if ($apiResult->isOk()) {
                    return true;
                }
                throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
            } catch (SystemException|GuzzleException|\Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws ExpertsenderNotAllowedException
     * @throws ExpertsenderServiceException
     */
    public function sendChangePasswordByProfile(User $user): bool
    {
        if (!$user->allowedEASend()) {
            throw new ExpertsenderNotAllowedException('эл. почта не подтверждена, отправка писем не возможна');
        }
        try {
            $receiver = new Receiver($user->getEmail());
            $apiResult = $this->client->sendTransactional(7073, $receiver);
            if ($apiResult->isOk()) {
                return true;
            }
            throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
        } catch (ExpertSenderException|GuzzleException $e) {
            throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param User   $user
     * @param string $backUrl
     *
     * @return bool
     * @throws ExpertsenderNotAllowedException
     * @throws ExpertsenderServiceException
     */
    public function sendForgotPassword(User $user, string $backUrl = ''): bool
    {
        if (!$user->allowedEASend()) {
            throw new ExpertsenderNotAllowedException('эл. почта не подтверждена, отправка писем не возможна');
        }
        if (!empty($user->getEmail())) {
            try {
                /** хеш строка для подтверждения мыла */
                /** @var ConfirmCodeService $confirmService */
                $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $confirmService::setGeneratedHash($user->getEmail(), 'email_forgot');
                $receiver = new Receiver($user->getEmail());
                $backUrlText = !empty($backUrl) ? '&backurl=' . $backUrl . '&user_id=' . $user->getId() : '';
                $snippets = [
                    new Snippet('user_name', $user->getName(), true),
                    new Snippet('link',
                        (new FullHrefDecorator('/personal/forgot-password/?hash=' . $confirmService::getGeneratedCode('email_forgot') . '&email=' . $user->getEmail() . $backUrlText))->getFullPublicPath(),
                        true),
                ];
                $apiResult = $this->client->sendTransactional(7072, $receiver, $snippets);
                if ($apiResult->isOk()) {
                    return true;
                }
                throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
            } catch (ExpertSenderException|GuzzleException|ApplicationCreateException|\Exception $e) {
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws ExpertsenderNotAllowedException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ExpertsenderServiceException
     */
    public function sendChangeEmail(User $oldUser, User $curUser): bool
    {
        $continue = true;
        $expertSenderId = 0;
        if (!empty($oldUser->getEmail())) {
            if (!$oldUser->allowedEASend()) {
                throw new ExpertsenderNotAllowedException('эл. почта не подтверждена, отправка писем не возможна');
            }

            $continue = false;
            /** отправка почты на старый email */
            try {
                $receiver = new Receiver($oldUser->getEmail());
                $apiResult = $this->client->sendTransactional(7070, $receiver);
                if ($apiResult->isOk()) {
                    $continue = true;
                }

                /** получение id подписчика по старому email */
                $userIdResult = $this->client->getUserId($oldUser->getEmail());
                if ($userIdResult->isOk()) {
                    $expertSenderId = $userIdResult->getId();
                }
            } catch (GuzzleException|\Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        if ($continue && !empty($curUser->getEmail())) {
            try {
                $continue = false;

                if ($expertSenderId > 0) {
                    $addUserToList = new AddUserToList();
                    $addUserToList->setForce(true);
                    $addUserToList->setMode('AddAndUpdate');
                    $addUserToList->setListId(178);
                    $addUserToList->setEmail($curUser->getEmail());
                    $addUserToList->setId($expertSenderId);

                    $apiResult = $this->client->addUserToList($addUserToList);
                    if ($apiResult->isOk()) {
                        $continue = true;
                    } else {
                        throw new ExpertsenderServiceException($apiResult->getErrorMessage(),
                            $apiResult->getErrorCode());
                    }
                } else {
                    /** если нет старой почты или не нашли на сайте регистрируем в сендере */
                    if($this->sendEmailAfterRegister($curUser, ['isReg' => 0, 'type' => 'email_change_email'])) {
                        $continue = false;
                    }
                }

                if ($continue) {
                    /** отправка почты на новый email, отправляем именно при смене, при регистрации еще подтвердить надо */
                    $receiver = new Receiver($curUser->getEmail());
                    $apiResult = $this->client->sendTransactional(7071, $receiver);
                    if ($apiResult->isOk()) {
                        return true;
                    }
                    throw new ExpertsenderServiceException($apiResult->getErrorMessage(),
                        $apiResult->getErrorCode());
                }
            } catch (GuzzleException|\Exception $e) {
                $a = $e->getMessage();
                echo $a;
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
        if (!empty($user->getEmail())) {
            try {
                $expertSenderId = 0;
                $userIdResult = $this->client->getUserId($user->getEmail());
                if ($userIdResult->isOk()) {
                    $expertSenderId = $userIdResult->getId();
                }

                if ($expertSenderId > 0) {
                    $addUserToList = new AddUserToList();
                    $addUserToList->setForce(true);
                    $addUserToList->setMode('AddAndUpdate');
                    $addUserToList->setTrackingCode('all_popup');
                    $addUserToList->setListId(178);
                    $addUserToList->setId($expertSenderId);
                    /** флаг подписки на новости */
                    $addUserToList->addProperty(new Property(23, 'boolean', true));

                    $apiResult = $this->client->addUserToList($addUserToList);
                    if ($apiResult->isOk()) {
                        return true;
                    }
                    throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
                }

                /** если не нашли id по почте регистрируем в сендере */
                return $this->sendEmailAfterRegister($user, ['isReg' => 0, 'type' => 'email_subscribe', 'subscribe'=>true]);
            } catch (GuzzleException|\Exception $e) {
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
        if (!empty($user->getEmail())) {
            try {
                $expertSenderId = 0;
                $userIdResult = $this->client->getUserId($user->getEmail());
                if ($userIdResult->isOk()) {
                    $expertSenderId = $userIdResult->getId();
                }

                if ($expertSenderId > 0) {
                    $addUserToList = new AddUserToList();
                    $addUserToList->setForce(true);
                    $addUserToList->setMode('AddAndUpdate');
                    $addUserToList->setTrackingCode('all_popup');
                    $addUserToList->setListId(178);
                    $addUserToList->setId($expertSenderId);
                    /** флаг подписки на новости */
                    $addUserToList->addProperty(new Property(23, 'boolean', 0));

                    $apiResult = $this->client->addUserToList($addUserToList);
                    if ($apiResult->isOk()) {
                        return true;
                    }

                    throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
                }
                return true;
            } catch (GuzzleException|\Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return false;
    }

    /**
     * @param string $email
     *
     * @return bool
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

        if (\in_array(178, $activeLists, true)) {
            return true;
        }
        return false;
    }

    /**
     * @param Order $order
     *
     * @return int
     * @throws ApplicationCreateException
     * @throws ExpertsenderServiceException
     */
    public function sendOrderNewEmail(Order $order): int
    {
        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        if (!$email = $orderService->getOrderPropertyByCode($order, 'EMAIL')->getValue()) {
            throw new ExpertsenderServiceException('order email is empty');
        }

        $properties = $orderService->getOrderPropertiesByCode($order, [
            'NAME',
            'DELIVERY_DATE',
            'PHONE',
            'BONUS_COUNT',
            'USER_REGISTERED',
        ]);

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

        $isOnlinePayment = false;
        try {
            $orderService->getOnlinePayment($order);
            $isOnlinePayment = true;
        } catch (NotFoundException $e) {
            //не требуется
        }

        if ($properties['USER_REGISTERED'] === BitrixUtils::BX_BOOL_TRUE) {
            // зарегистрированный пользователь
            if ($isOnlinePayment) {
                // онлайн-оплата
                $transactionId = 7103;
            } else {
                // оплата при получении
                $transactionId = 7104;
            }
        } else {
            // незарегистрированный пользователь
            /* @todo вынести из сессии? */
            $snippets[] = new Snippet('login', $_SESSION['NEW_USER']['LOGIN']);
            $snippets[] = new Snippet('password', $_SESSION['NEW_USER']['PASSWORD']);
            if ($isOnlinePayment) {
                // онлайн-оплата
                $transactionId = 7150;
            } else {
                // оплата при получении
                $transactionId = 7148;
            }
        }

        $items = [];
        try {
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

                $items[] = '<Product>
                    <Name>' . $basketItem->getField('NAME') . '</Name>
                    <PicUrl>' . new FullHrefDecorator((string)$offer->getImages()->first()) . '</PicUrl>
                    <Link>' . new FullHrefDecorator($offer->getDetailPageUrl()) . '</Link>
                    <Price1>' . $basketItem->getBasePrice() . '</Price1>
                    <Price2>' . $basketItem->getPrice() . '</Price2>
                    <Amount>' . $basketItem->getQuantity() . '</Amount>
                </Product>';
            }
        } catch (NotFoundException $e) {
            throw new ExpertsenderServiceException($e->getMessage());
        }
        $items = '<Products>' . implode('', $items) . '</Products>';
        $snippets[] = new Snippet('alt_products', $items, true);

        try {
            $apiResult = $this->client->sendTransactional($transactionId, new Receiver($email), $snippets);
            if (!$apiResult->isOk()) {
                throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
            }
            unset($_SESSION['NEW_USER']);
            return $transactionId;
        } catch (GuzzleException|\Exception $e) {
            throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param Order $order
     *
     * @return int
     * @throws ApplicationCreateException
     * @throws ExpertsenderServiceException
     */
    public function sendOrderCompleteEmail(Order $order): int
    {
        /** @todo нужно юзануть проверку доступности отправки писем в ES(если письмо недоступно без конфирма мыла) - метод юзера - allowedEASend */
        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);

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

        $transactionId = 7122;

        try {
            $apiResult = $this->client->sendTransactional($transactionId, new Receiver($email), $snippets);
            if (!$apiResult->isOk()) {
                throw new ExpertsenderServiceException($apiResult->getErrorMessage(), $apiResult->getErrorCode());
            }

            return $transactionId;
        } catch (GuzzleException|\Exception $e) {
            throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
        }
    }
}
