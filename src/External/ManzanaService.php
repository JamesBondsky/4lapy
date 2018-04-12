<?php

namespace FourPaws\External;

use Exception;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaCardIsNotFound;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Exception\TooManyActiveCardFound;
use FourPaws\External\Interfaces\ManzanaServiceInterface;
use FourPaws\External\Manzana\Exception\AuthenticationException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Exception\ContactNotFoundException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\Manzana\Model\Card;
use FourPaws\External\Manzana\Model\CardByContractCards;
use FourPaws\External\Manzana\Model\Cards;
use FourPaws\External\Manzana\Model\CardsByContractCards;
use FourPaws\External\Manzana\Model\CardValidateResult;
use FourPaws\External\Manzana\Model\Cheque;
use FourPaws\External\Manzana\Model\ChequeByContractCheques;
use FourPaws\External\Manzana\Model\ChequeItem;
use FourPaws\External\Manzana\Model\ChequeItems;
use FourPaws\External\Manzana\Model\Cheques;
use FourPaws\External\Manzana\Model\ChequesByContractCheques;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\Manzana\Model\Clients;
use FourPaws\External\Manzana\Model\Contact;
use FourPaws\External\Manzana\Model\Contacts;
use FourPaws\External\Manzana\Model\ParameterBag;
use FourPaws\External\Manzana\Model\ReferralParams;
use FourPaws\External\Manzana\Model\Referrals;
use FourPaws\External\Manzana\Model\Result;
use FourPaws\External\Manzana\Model\ResultXmlFactory;
use FourPaws\External\Traits\ManzanaServiceTrait;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ManzanaService
 *
 * @package FourPaws\External
 */
class ManzanaService implements LoggerAwareInterface, ManzanaServiceInterface
{
    use ManzanaServiceTrait;

    public const METHOD_AUTHENTICATE = 'Authenticate';

    public const METHOD_EXECUTE = 'Execute';

    public const CONTRACT_ADVANCED_BALANCE = 'advanced_balance';

    public const CONTRACT_CARD_ATTACH = 'card_attach';

    public const CONTRACT_CARD_VALIDATE = 'card_validate';

    public const CONTRACT_CARDS = 'cards';

    public const CONTRACT_CHANGE_CARD = 'contact_card_update';

    public const CONTRACT_CLIENT_SEARCH = 'client_search';

    public const CONTRACT_CONTACT = 'contact';

    public const CONTRACT_CONTACT_CHEQUES = 'contact_cheques';

    public const CONTRACT_CONTACT_REFERRAL_CARDS = 'Contact_Referral_Cards';

    public const CONTRACT_CONTACT_UPDATE = 'contact_update';

    public const CONTRACT_CHEQUE_ITEMS = 'cheque_items';

    public const CONTRACT_SEARCH_CARD_BY_NUMBER = 'search_cards_by_number';

    public const CONTRACT_CHEQUES = 'cheques';

    protected $sessionId;

    protected $cards = [];

    /**
     * Отправка телефона
     *
     * - после верификации номера телефона
     * - заказ в один клик
     * - регистрация бонусной карты в ЛК магазина
     *
     * @param string $phone
     *
     * @return string
     *
     * @throws ExecuteException
     */
    public function sendPhone(string $phone): string
    {
        if(empty($phone)){
            return '';
        }
        $bag = new ParameterBag(
            [
                'maxresultsnumber' => '1',
                'mobilephone' => $phone,
            ]
        );

        $result = $this->execute(self::CONTRACT_SEARCH_CARD_BY_NUMBER, $bag->getParameters());

        return $result;
    }

    /**
     * @param string $contract
     * @param array $parameters
     *
     * @return string
     *
     * @throws ExecuteException
     */
    protected function execute(string $contract, array $parameters = []): string
    {
        try {
            $sessionId = $this->authenticate();

            $arguments = [
                'sessionId' => $sessionId,
                'contractName' => $contract,
                'parameters' => $parameters,
            ];

            $result = $this->client->call(self::METHOD_EXECUTE, ['request_options' => $arguments]);

            $result = $result->ExecuteResult->Value;
        } catch (Exception $e) {
            unset($this->sessionId);

            try {
                /** @noinspection PhpUndefinedFieldInspection */
                $detail = $e->detail->details->description;
            } catch (\Throwable $e) {
                $detail = 'none';
            }

            $this->logger->error(
                sprintf(
                    'Manzana execute error with contract id %s: %s, detail: %s, parameters: %s',
                    $contract,
                    $e->getMessage(),
                    $detail,
                    var_export($parameters, true)
                )
            );

            throw new ExecuteException(
                sprintf('Execute error: %s, detail: %s', $e->getMessage(), $detail), $e->getCode(), $e
            );
        }

        return $result;
    }

    /**
     * @return string
     *
     * @throws AuthenticationException
     */
    protected function authenticate(): string
    {
        if ($this->sessionId) {
            return $this->sessionId;
        }

        $arguments = [
            'login' => $this->parameters['login'],
            'password' => $this->parameters['password'],
            'ip' => ($_SERVER['HTTP_X_FORWARDED_FOR'] ?: $_SERVER['REMOTE_ADDR']) ?? '127.0.0.1',
        ];

        try {
            $this->sessionId = $this->client->call(
                self::METHOD_AUTHENTICATE,
                ['request_options' => $arguments]
            )->AuthenticateResult->SessionId;
        } catch (Exception $e) {
            throw new AuthenticationException(sprintf('Auth error: %s', $e->getMessage()), $e->getCode(), $e);
        }

        return $this->sessionId;
    }

    /**
     * Обновление/создание контакта
     *
     * - после регистрации (для существующего и нового относительно ML)
     * - после оформления заказа (зарегистрированный/незарегистрированный)
     * - после оформления заказа в 1 клик (зарегистрированный/незарегистрированный)
     * - назначение адреса доставки основным
     * - изменение основного адреса доставки
     * - изменение профиля
     * - сохранение карточки питомца (передача типа питомца)
     * - удаление типа питомца (изменение типа питомца/удаление питомца в случае, если больше нет питомцев такого типа)
     * - замена виртуальной карты на физическую в ЛК
     *
     * @param Client $contact
     *
     * @return Client
     *
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
     */
    public function updateContact(Client $contact): Client
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $bag = new ParameterBag($this->serializer->toArray($contact));

        try {
            $rawResult = $this->execute(self::CONTRACT_CONTACT_UPDATE, $bag->getParameters());
            $result = ResultXmlFactory::getContactResultFromXml($this->serializer, $rawResult);

            if ($result->isError()) {
                throw new ContactUpdateException($result->getResult());
            }
            $contact->contactId = $result->getContactId();
        } catch (ContactUpdateException $e) {
            throw new ContactUpdateException($e->getMessage());
        } catch (Exception $e) {
            throw new ManzanaServiceException($e->getMessage());
        }

        return $contact;
    }

    /**
     * @param User|null $user
     *
     * @return Client
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ManzanaServiceContactSearchNullException
     * @throws ManzanaServiceContactSearchMoreOneException
     * @throws ManzanaServiceException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws NotAuthorizedException
     */
    public function getContactByUser(User $user = null): Client
    {
        if (!($user instanceof User)) {
            $user = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class)->getCurrentUser();
        }
        if(empty($user->getManzanaNormalizePersonalPhone())){
            throw new ManzanaServiceContactSearchNullException('телефон не задан');
        }

        return $this->getContactByPhone(
            $user->getManzanaNormalizePersonalPhone()
        );
    }

    /**
     * @param string $phone
     *
     * @return Client
     * @throws ManzanaServiceContactSearchMoreOneException
     * @throws ManzanaServiceContactSearchNullException
     * @throws ManzanaServiceException
     */
    public function getContactByPhone(string $phone): Client
    {
        if(empty($phone)){
            throw new ManzanaServiceContactSearchNullException('телефон не задан');
        }
        /** @var Clients $currentClient */
        /** @noinspection PhpUndefinedMethodInspection */
        $clients = $this->getUserDataByPhone($phone)->clients->toArray();
        $countClients = \count($clients);
        if ($countClients === 1) {
            return current($clients);
        }

        if ($countClients > 1) {
            $this->logger->critical('Найдено больше одного пользователя с телефоном ' . $phone);
            throw new ManzanaServiceContactSearchMoreOneException('Найдено больше одного пользователя');
        }

        throw new ManzanaServiceContactSearchNullException('Пользователей не найдено');
    }

    /**
     * Получение данных пользователя
     *
     * - при регистрации после ввода номера телефона
     * - заказ в один клик
     * - заказ
     *
     * @param string $phone
     *
     * @return Clients
     *
     * @throws ManzanaServiceException
     */
    public function getUserDataByPhone(string $phone): Clients
    {
        if(empty($phone)){
            throw new ManzanaServiceContactSearchNullException('телефон не задан');
        }
        $bag = new ParameterBag(
            [
                'maxresultsnumber' => '5',
                'mobilephone' => $phone,
            ]
        );

        try {
            $result = $this->execute(self::CONTRACT_CLIENT_SEARCH, $bag->getParameters());

            $clients = $this->serializer->deserialize($result, Clients::class, 'xml');
        } catch (Exception $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $clients;
    }

    /**
     * Передача номера бонусной карты реферала для получения Contact_ID реферала
     *
     * - первый шаг заполнения формы добавления реферала
     *
     * @param ReferralParams $referralParams
     *
     * @return string
     * @throws ManzanaServiceException
     */
    public function addReferralByBonusCard(ReferralParams $referralParams): string
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $bag = new ParameterBag($this->serializer->toArray($referralParams));

        try {
            $rawResult = $this->execute(self::CONTRACT_CARD_ATTACH, $bag->getParameters());

            $result = ResultXmlFactory::getReferralCardAttachResultFromXml($this->serializer, $rawResult);
            if (!$result->isError()) {
                $result = $result->getContactId();
            }
        } catch (Exception $e) {
            try {
                /** @noinspection PhpUndefinedFieldInspection */
                $detail = $e->detail->details->description;
            } catch (\Throwable $e) {
                $detail = 'none';
            }

            throw new ManzanaServiceException($detail);
        }

        return $result;
    }

    /**
     * Получение данных о рефералах заводчика
     *
     * - переход в раздел «Реферальная программа» в ЛК покупателя
     *
     * @param User $user
     *
     * @return array
     * @throws ManzanaServiceContactSearchNullException
     * @throws ManzanaServiceContactSearchMoreOneException
     * @throws ServiceNotFoundException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws ManzanaServiceException
     * @throws ServiceCircularReferenceException
     */
    public function getUserReferralList(User $user = null): array
    {
        $contact_id = $this->getContactIdByUser($user);
        $referrals = [];
        if ($contact_id > 0) {
            $bag = new ParameterBag(
                [
                    'contact_id' => $contact_id,
                ]
            );

            try {
                $result = $this->execute(self::CONTRACT_CONTACT_REFERRAL_CARDS, $bag->getParameters());
                /** @var Referrals $res */
                $res = $this->serializer->deserialize($result, Referrals::class, 'xml');
                /** @noinspection PhpUndefinedMethodInspection */
                $referrals = $res->referrals->toArray();
            } catch (Exception $e) {
                throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $referrals;
    }

    /**
     * @param User|null $user
     *
     * @return string
     * @throws ManzanaServiceContactSearchNullException
     * @throws ManzanaServiceContactSearchMoreOneException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ManzanaServiceException
     * @throws ConstraintDefinitionException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     */
    public function getContactIdByUser(User $user = null): string
    {
        if (!($user instanceof User)) {
            $user = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class)->getCurrentUser();
        }

        if(empty($user->getManzanaNormalizePersonalPhone())){
            throw new ManzanaServiceContactSearchNullException('Нет телефона');
        }
        return $this->getContactIdByPhone(
            $user->getManzanaNormalizePersonalPhone()
        );
    }

    /**
     * @param string $phone
     *
     * @return string
     * @throws ManzanaServiceContactSearchNullException
     * @throws ManzanaServiceContactSearchMoreOneException
     * @throws ManzanaServiceException
     */
    public function getContactIdByPhone(string $phone): string
    {
        if(empty($phone)){
            throw new ManzanaServiceContactSearchNullException('не указан телефон');
        }
        return (string)$this->getContactByPhone($phone)->contactId;
    }

    /**
     * Передача номера бонусной карты для проверки валидности
     * Контракт в ML: card_validate
     *
     * - форма замены бонусной карты
     * - регистрация бонусной карты сотрудником магазина в ЛК магазина
     *
     * @param string $cardNumber
     *
     * @return bool
     *
     * @throws ManzanaServiceException
     */
    public function validateCardByNumber(string $cardNumber): bool
    {
        $cardValidateResult = $this->validateCardByNumberRaw($cardNumber);

        return $cardValidateResult->isValid ? true : false;
    }

    /**
     * Проверка валидности карты и получение ее ID
     * Контракт в ML: card_validate
     *
     * - форма замены бонусной карты
     * - регистрация бонусной карты сотрудником магазина в ЛК магазина
     *
     * @param string $cardNumber
     *
     * @return CardValidateResult
     * @throws ManzanaServiceException
     */
    public function validateCardByNumberRaw(string $cardNumber): CardValidateResult
    {
        $cardValidateResult = null;
        $bag = new ParameterBag(['cardnumber' => $cardNumber]);
        try {
            $result = $this->execute(self::CONTRACT_CARD_VALIDATE, $bag->getParameters());
            /** @var CardValidateResult $cardValidateResult */
            $cardValidateResult = $this->serializer->deserialize($result, CardValidateResult::class, 'xml');
        } catch (\Throwable $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $cardValidateResult;
    }

    /**
     * Получение данных о держателе бонусной карты
     *
     * @param string $cardNumber
     *
     * -
     * - ЛК магазина, просмотр истории по карте
     *
     * @return Card
     *
     * @throws ManzanaServiceException
     * @throws CardNotFoundException
     */
    public function searchCardByNumber(string $cardNumber): Card
    {
        $card = null;
        $bag = new ParameterBag(['cardnumber' => $cardNumber]);

        try {
            $result = $this->execute(self::CONTRACT_SEARCH_CARD_BY_NUMBER, $bag->getParameters());
            $card = $this->serializer->deserialize($result, Cards::class, 'xml')->cards[0];
        } catch (Exception $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }

        if (!($card instanceof Card)) {
            throw new CardNotFoundException(sprintf('Карта %s не найдена', $cardNumber));
        }

        return $card;
    }

    /**
     * Получение контакта по Contact_ID
     *
     * @param $contactId
     *
     * @return Contact
     *
     * @throws ManzanaServiceException
     * @throws ContactNotFoundException
     */
    public function getContactByContactId($contactId): Contact
    {
        $contact = null;
        $bag = new ParameterBag(['contact_id' => $contactId]);

        try {
            $result = $this->execute(self::CONTRACT_CONTACT, $bag->getParameters());

            $contact = $this->serializer->deserialize($result, Contacts::class, 'xml')->contacts[0];
        } catch (Exception $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }

        if (!($contact instanceof Contact)) {
            throw new ContactNotFoundException(sprintf('Контакт %s не найден', $contactId));
        }

        return $contact;
    }

    /**
     * @param string $cardID
     * @param string $contactId
     *
     * @return CardByContractCards|null
     * @throws ManzanaServiceException
     */
    public function getCardInfo(string $cardID, string $contactId): ?CardByContractCards
    {
        $cards = $this->getCardsByContactId($contactId);
        if (\is_array($cards) && !empty($cards)) {
            /** @var CardByContractCards $card */
            foreach ($cards as $card) {
                if ($card->cardNumber === $cardID) {
                    return $card;
                }
            }

        }

        return null;
    }

    /**
     * @param $contactId
     *
     * @return array|CardByContractCards[]
     * @throws ManzanaServiceException
     */
    public function getCardsByContactId($contactId): array
    {
        if (!empty($this->cards[$contactId])) {
            $cards = $this->cards[$contactId];
        } else {
            $bag = new ParameterBag(['contact_id' => $contactId]);
            try {
                $result = $this->execute(self::CONTRACT_CARDS, $bag->getParameters());
                /** @var CardsByContractCards $cards */
                $this->cards[$contactId] =
                $cards = $this->serializer->deserialize($result, CardsByContractCards::class, 'xml')->cards->toArray();
            } catch (Exception $e) {
                throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $cards;
    }

    /**
     * @param string $contactId
     *
     * @return Cheque[]
     * @throws ManzanaServiceException
     */
    public function getCheques(string $contactId): array
    {
        $bag = new ParameterBag(['contact_id' => $contactId]);
        try {
            $result = $this->execute(self::CONTRACT_CONTACT_CHEQUES, $bag->getParameters());
            /** @var Cheques $resCheques */
            $resCheques = $this->serializer->deserialize($result, Cheques::class, 'xml');
            /** @var $resCheques ->cheques $cheques */
            /** @noinspection PhpUndefinedMethodInspection */
            /** метод есть так как ArrayCollection */
            $cheques = $resCheques->cheques->toArray();
        } catch (Exception $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $cheques;
    }

    /**
     * @param string $cardId
     *
     * @return ChequeByContractCheques[]|array
     * @throws ManzanaServiceException
     */
    public function getChequesByCardId(string $cardId): array
    {
        $bag = new ParameterBag(['card_id' => $cardId]);
        try {
            $result = $this->execute(self::CONTRACT_CHEQUES, $bag->getParameters());
            $cheques =
                $this->serializer->deserialize($result, ChequesByContractCheques::class, 'xml')->cheques->toArray();
        } catch (Exception $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $cheques;
    }

    /**
     * @param string $contactId
     *
     * @return Cheque[]|array
     * @throws ManzanaServiceException
     */
    public function getChequesByContactId(string $contactId): array
    {
        return $this->getCheques($contactId);
    }

    /**
     * @param string $chequeId
     *
     * @return ChequeItem[]
     * @throws ManzanaServiceException
     */
    public function getItemsByCheque(string $chequeId): array
    {
        $bag = new ParameterBag(['cheque_id' => $chequeId]);
        try {
            $result = $this->execute(self::CONTRACT_CHEQUE_ITEMS, $bag->getParameters());
            /** @var ChequeItems $resChequeItems */
            $resChequeItems = $this->serializer->deserialize($result, ChequeItems::class, 'xml');
            /** @var ChequeItem[] $resCheques ->cheques */
            /** @noinspection PhpUndefinedMethodInspection*/
            /** метод есть, так как ArrayCollection */
            $chequeItems = $resChequeItems->chequeItems->toArray();
        } catch (Exception $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $chequeItems;
    }

    /**
     * Обновление/создание контакта. Очередь в rabbit.
     *
     * @param Client $contact
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     */
    public function updateContactAsync(Client $contact)
    {
        /** @noinspection MissingService */
        $producer = App::getInstance()->getContainer()->get('old_sound_rabbit_mq.manzana_update_producer');
        $producer->publish($this->serializer->serialize($contact, 'json'));
    }

    /**
     * @param Client $client
     */
    public function updateUserCardByClient(Client $client)
    {
        try {
            $userRepository = $this->userRepository;

            $user = $userRepository->find($userRepository->findIdentifierByRawLogin($client->phone));

            if (!$user instanceof User) {
                throw new UsernameNotFoundException(sprintf('User with phone %s is not found', $client->phone));
            }

            $card = $this->getActiveCardByContactId($client->contactId);

            $userRepository->update($user->setDiscountCardNumber($card->cardNumber));
        } catch (ManzanaCardIsNotFound $e) {
            $this->logger->error($e->getMessage());
        } catch (Exception $e) {
            $this->logger->error(sprintf('Manzana user card update error: %s', $e->getMessage()));
        }
    }

    /**
     * @param string $contactId
     * @return CardByContractCards
     * @throws TooManyActiveCardFound
     * @throws ManzanaServiceException
     *
     * @throws ManzanaCardIsNotFound
     */
    private function getActiveCardByContactId(string $contactId): CardByContractCards
    {
        $cards = $this->getCardsByContactId($contactId);
        $activeCards = array_filter($cards, function (CardByContractCards $card) {
            return $card->status === CardByContractCards::CARD_STATUS_ACTIVE;
        });

        if (\count($activeCards) === 1) {
            return $activeCards[0];
        }

        if (\count($activeCards) > 1) {
            throw new TooManyActiveCardFound(sprintf('Card is not found to user with contact id: %s', $contactId));
        }

        throw new ManzanaCardIsNotFound(sprintf('Card is not found to user with contact id: %s', $contactId));
    }

    /**
     * @param string $card_from
     * @param string $card_to
     *
     * @return bool
     * @throws ManzanaServiceException
     */
    public function changeCard(string $card_from, string $card_to): bool
    {
        try {
            $bag = new ParameterBag(['card_from' => $card_from, 'card_to' => $card_to]);
            $rawResult = $this->execute(self::CONTRACT_CHANGE_CARD, $bag->getParameters());
            /** @var Result $result */
            preg_match_all("/<result>(.+?)<\/result>/is", $rawResult, $matches);
            $result = end($matches);
            if(\is_array($result)){
                $result=end($result);
            }
            $res = preg_match("/.*успешно.*/isu", $result);
            return $res !== false && $res > 0;
        } catch (Exception $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
