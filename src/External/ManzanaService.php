<?php

namespace FourPaws\External;

use Bitrix\Main\Type\Date;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Interfaces\ManzanaServiceInterface;
use FourPaws\External\Manzana\Exception\AuthenticationException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Exception\ContactNotFoundException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\Manzana\Model\Card;
use FourPaws\External\Manzana\Model\Cards;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\Manzana\Model\Clients;
use FourPaws\External\Manzana\Model\Contact;
use FourPaws\External\Manzana\Model\Contacts;
use FourPaws\External\Manzana\Model\ParameterBag;
use FourPaws\External\Manzana\Model\ResultXmlFactory;
use FourPaws\External\Traits\ManzanaServiceTrait;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\EmptyDateException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
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
    
    const METHOD_AUTHENTICATE             = 'Authenticate';
    
    const METHOD_EXECUTE                  = 'Execute';
    
    const CONTRACT_ADVANCED_BALANCE       = 'advanced_balance';
    
    const CONTRACT_CARD_ATTACH            = 'card_attach';
    
    const CONTRACT_CARD_VALIDATE          = 'card_validate';
    
    const CONTRACT_CARDS                  = 'cards';
    
    const CONTRACT_CHANGE_CARD            = 'change_card';
    
    const CONTRACT_CLIENT_SEARCH          = 'client_search';
    
    const CONTRACT_CONTACT                = 'contact';
    
    const CONTRACT_CONTACT_CHEQUES        = 'contact_cheques';
    
    const CONTRACT_CONTACT_REFERRAL_CARDS = 'contact_Referral_Cards';
    
    const CONTRACT_CONTACT_UPDATE         = 'contact_update';
    
    const CONTRACT_CHEQUE_ITEMS           = 'cheque_items';
    
    const CONTRACT_SEARCH_CARD_BY_NUMBER  = 'search_cards_by_number';
    
    protected $sessionId;
    
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
     * @throws ManzanaServiceException
     */
    public function sendPhone(string $phone) : string
    {
        $bag = new ParameterBag(
            [
                'maxresultsnumber' => '1',
                'mobilephone'      => $phone,
            ]
        );
        
        $result = $this->execute(self::CONTRACT_SEARCH_CARD_BY_NUMBER, $bag->getParameters());
        
        return $result;
    }
    
    /**
     * @param string $contract
     * @param array  $parameters
     *
     * @return string
     *
     * @throws AuthenticationException
     * @throws ExecuteException
     */
    protected function execute(string $contract, array $parameters = []) : string
    {
        try {
            $sessionId = $this->authenticate();
            
            $arguments = [
                'sessionId'    => $sessionId,
                'contractName' => $contract,
                'parameters'   => $parameters,
            ];
            
            $result = $this->client->call(self::METHOD_EXECUTE, ['request_options' => $arguments]);
            
            $result = $result->ExecuteResult->Value;
        } catch (\Exception $e) {
            unset($this->sessionId);
            
            try {
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
                    var_export($parameters)
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
    protected function authenticate() : string
    {
        if ($this->sessionId) {
            return $this->sessionId;
        }
        
        $arguments = [
            'login'    => $this->parameters['login'],
            'password' => $this->parameters['password'],
            'ip'       => $_SERVER['HTTP_X_FORWARDED_FOR'] ?: $_SERVER['REMOTE_ADDR'],
        ];
        
        try {
            $this->sessionId = $this->client->call(
                self::METHOD_AUTHENTICATE,
                ['request_options' => $arguments]
            )->AuthenticateResult->SessionId;
        } catch (\Exception $e) {
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
    public function updateContact(Client $contact) : Client
    {
        $bag = new ParameterBag($this->serializer->toArray($contact));
        
        try {
            $rawResult = $this->execute(self::CONTRACT_CONTACT_UPDATE, $bag->getParameters());
            $result    = ResultXmlFactory::getContactResultFromXml($this->serializer, $rawResult);
            
            if ($result->isError()) {
                throw new ContactUpdateException($result->getResult());
            }
            
            $contact->contactId = $result->getContactId();
        } catch (ContactUpdateException $e) {
            throw new ContactUpdateException($e->getMessage());
        } catch (\Exception $e) {
            throw new ManzanaServiceException($e->getMessage());
        }
        
        return $contact;
    }
    
    /**
     * @param Client    $client
     * @param User|null $user
     *
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ConstraintDefinitionException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     */
    public function setClientPersonalDataByCurUser(&$client, User $user = null)
    {
        if (!($user instanceof User)) {
            $user = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class)->getCurrentUser();
        }
        
        try {
            $birthday = $user->getBirthday();
            if ($birthday instanceof Date) {
                $client->birthDate = $birthday->format('d.m.Y');
            }
        } catch (EmptyDateException $e) {
        }
        $client->phone              = $user->getPersonalPhone();
        $client->firstName          = $user->getName();
        $client->secondName         = $user->getSecondName();
        $client->lastName           = $user->getLastName();
        $client->genderCode         = $user->getGender();
        $client->email              = $user->getEmail();
        $client->plLogin            = $user->getLogin();
        $client->plRegistrationDate = $user->getDateRegister()->format('d.m.Y');
    }
    
    /**
     * @param Client  $client
     * @param Address $address
     */
    public function setClientAddress(&$client, Address $address)
    {
        /** неоткуда взять область для обновления
         * $client->addressStateOrProvince = '';*/
        $client->addressCity   = $address->getCity();//Город
        $client->address       = $address->getStreet();//Улица
        $client->addressLine2  = $address->getHouse();//Дом
        $client->addressLine3  = $address->getHousing();//Корпус
        $client->plAddressFlat = $address->getFlat();//Квартира
    }
    
    /**
     * @param User|null $user
     *
     * @return int
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ManzanaServiceException
     * @throws ConstraintDefinitionException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     */
    public function getContactIdByCurUser(User $user = null) : int
    {
        if(!($user instanceof User)){
            $user = App::getInstance()
                       ->getContainer()
                       ->get(CurrentUserProviderInterface::class)
                       ->getCurrentUser();
        }
        return $this->getContactIdByPhone(
            $user->getPersonalPhone()
        );
    }
    
    /**
     * @param Client $client
     * @param array  $types
     */
    public function setClientPets(&$client, array $types)
    {
        /** @todo set actual types*/
        $baseTypes        =
            [
                'bird',
                'cat',
                'dog',
                'fish',
                'rodent',
            ];
        $client->ffBird   = \in_array('bird', $types, true) ? 1 : 0;
        $client->ffCat    = \in_array('cat', $types, true) ? 1 : 0;
        $client->ffDog    = \in_array('dog', $types, true) ? 1 : 0;
        $client->ffFish   = \in_array('fish', $types, true) ? 1 : 0;
        $client->ffRodent = \in_array('rodent', $types, true) ? 1 : 0;
        $others           = 0;
        if (\is_array($types) && !empty($types)) {
            foreach ($types as $type) {
                if (!\in_array($type, $baseTypes, true)) {
                    $others = 1;
                    break;
                }
            }
            
        }
        $client->ffOthers = $others;
    }
    
    /**
     * @param string $phone
     *
     * @return int
     * @throws ManzanaServiceException
     */
    public function getContactIdByPhone(string $phone) : int
    {
        $contactId = -1;
        /** @var Clients $currentClient */
        $clients      = $this->getUserDataByPhone($phone);
        $countClients = \count($clients->clients);
        if ($countClients === 1) {
            /** @var Client $currentClient */
            $currentClient = current($clients->clients);
            $contactId     = (int)$currentClient->contactId;
        } elseif ($countClients > 1) {
            $this->logger->critical('Найдено больше одного пользователя с телефоном ' . $phone);
        } else {
            $contactId = 0;
        }
        
        return $contactId;
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
    public function getUserDataByPhone(string $phone) : Clients
    {
        $bag = new ParameterBag(
            [
                'maxresultsnumber' => '5',
                'mobilephone'      => $phone,
            ]
        );
        
        try {
            $result = $this->execute(self::CONTRACT_CLIENT_SEARCH, $bag->getParameters());
            
            $clients = $this->serializer->deserialize($result, Clients::class, 'xml');
        } catch (\Exception $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }
        
        return $clients;
    }
    
    /**
     * Получение виртуальной бонусной карты
     *
     * - заказ в один клик
     * - заказ
     */
    public function getCard()
    {
    
    }
    
    /**
     * Получение покупок и заказов пользователя из ML
     *
     * - ЛК покупателя, переход в список последних заказов
     * - ЛК магазина, просмотр истории по карте
     */
    public function getOrderList()
    {
    
    }
    
    /**
     * Получение детальной информации о заказе
     *
     * - ЛК покупателя, переход в карточку заказа
     * - ЛК магазина, просмотр истории по карте, детализация чека
     */
    public function getOrderDetail()
    {
    
    }
    
    /**
     * Получение данных о количестве активных бонусов и размере бонуса (% от стоимости товара, который возвращается
     * баллами на бонусную карту пользователя)
     *
     * - после авторизации (?!)
     *
     */
    public function getActiveBonus()
    {
    
    }
    
    /**
     * Получение данных о расширенном балансе бонусной карты пользователя
     *
     * - переход в раздел "Бонусы" в ЛК
     * - ЛК магазина, просмотр истории по карте
     */
    public function getAdvancedBalance()
    {
    
    }
    
    /**
     * Передача номера бонусной карты реферала для получения Contact_ID реферала
     *
     * - первый шаг заполнения формы добавления реферала
     */
    public function addReferralByBonusCard() : string
    {
    
    }
    
    /**
     * Получение информации о реферале по Contact_ID
     */
    public function getReferralByContactId()
    {
    
    }
    
    /**
     * Получение данных о рефералах заводчика
     *
     * - переход в раздел «Реферальная программа» в ЛК покупателя
     */
    public function getUserReferralList()
    {
    
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
    public function validateCardByNumber(string $cardNumber) : bool
    {
        $bag = new ParameterBag(['cardnumber' => $cardNumber]);
        
        try {
            $result = $this->execute(self::CONTRACT_CARD_VALIDATE, $bag->getParameters());
            $result = $result->cardid->__toString() !== '';
        } catch (\Throwable $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }
        
        return $result;
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
    public function searchCardByNumber(string $cardNumber) : Card
    {
        $card = null;
        $bag  = new ParameterBag(['cardnumber' => $cardNumber]);
        
        try {
            $result = $this->execute(self::CONTRACT_SEARCH_CARD_BY_NUMBER, $bag->getParameters());
            
            $card = $this->serializer->deserialize($result, Cards::class, 'xml')->cards[0];
        } catch (\Exception $e) {
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
    public function getContactByContactId($contactId) : Contact
    {
        $contact = null;
        $bag     = new ParameterBag(['contact_id' => $contactId]);
        
        try {
            $result = $this->execute(self::CONTRACT_CONTACT, $bag->getParameters());
            
            $contact = $this->serializer->deserialize($result, Contacts::class, 'xml')->contacts[0];
        } catch (\Exception $e) {
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }
        
        if (!($contact instanceof Contact)) {
            throw new ContactNotFoundException(sprintf('Контакт %s не найден', $contactId));
        }
        
        return $contact;
    }
    
    protected function clientSearch(array $data)
    {
    
    }
}
