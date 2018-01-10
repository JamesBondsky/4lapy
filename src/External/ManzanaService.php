<?php

namespace FourPaws\External;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
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
use FourPaws\External\Manzana\Model\Referrals;
use FourPaws\External\Manzana\Model\ResultXmlFactory;
use FourPaws\External\Traits\ManzanaServiceTrait;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
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
    
    const CONTRACT_CONTACT_REFERRAL_CARDS = 'Contact_Referral_Cards';
    
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
                    ''
                    //var_export($parameters)
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
     * Возвращает id, -1 если найдено больше 1 записи и 0 если не найдено записей
     * @param User|null $user
     *
     * @return string
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ManzanaServiceException
     * @throws ConstraintDefinitionException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     */
    public function getContactIdByCurUser(User $user = null) : string
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
     * @todo сделать выбрасывание исключение если найдено больше 1 записи и если найдено 0 записей
     * Возвращает id, -1 tесли найдено больше 1 записи и 0 если не найдено записей
     *
     * @param string $phone
     *
     * @return string
     * @throws ManzanaServiceException
     */
    public function getContactIdByPhone(string $phone) : string
    {
        try {
            $currentClient = $this->getContactByPhone($phone);
            $contactId     = (string)$currentClient->contactId;
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
            $contactId = -1;
        } catch (ManzanaServiceContactSearchNullException $e) {
            $contactId = 0;
        }
        
        return $contactId;
    }
    
    /**
     * Возвращает id, -1 если найдено больше 1 записи и 0 если не найдено записей
     *
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
    public function getContactByCurUser(User $user = null) : Client
    {
        if(!($user instanceof User)){
            $user = App::getInstance()
                       ->getContainer()
                       ->get(CurrentUserProviderInterface::class)
                       ->getCurrentUser();
        }
        return $this->getContactByPhone(
            $user->getPersonalPhone()
        );
    }
    
    /**
     * Возвращает FourPaws\External\Manzana\Model\Client
     *
     * @param string $phone
     *
     * @return Client
     * @throws ManzanaServiceContactSearchMoreOneException
     * @throws ManzanaServiceContactSearchNullException
     * @throws ManzanaServiceException
     */
    public function getContactByPhone(string $phone) : Client
    {
        /** @var Clients $currentClient */
        $clients      = $this->getUserDataByPhone($phone)->clients->toArray();
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
     *
     * @param array $params
     *
     * @return string
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws CardNotFoundException
     */
    public function addReferralByBonusCard(array $params = []) : string
    {
        if(empty($params['contact_id']) || empty($params['cardnumber'])){
            try {
                $manzanaItem = $this->getContactByCurUser();
                /** @noinspection NotOptimalIfConditionsInspection */
                if(empty($params['contact_id'])){
                    $manzanaParams['contact_id']     = $manzanaItem->contactId;
                }
                else{
                    $manzanaParams['contact_id']     = $params['contact_id'];
                }
                /** @noinspection NotOptimalIfConditionsInspection */
                if(empty($params['cardnumber'])){
                    $currentCard = current($manzanaItem->cards);
                    if(!($currentCard instanceof Card) || empty($currentCard->cardNumber)){
                        throw new CardNotFoundException('не найдена карта');
                    }
                    $manzanaParams['cardnumber']     = $currentCard->cardNumber;
                }
                else{
                    $manzanaParams['cardnumber']     = $params['cardnumber'];
                }
            } catch (ApplicationCreateException $e) {
            } catch (ManzanaServiceContactSearchMoreOneException $e) {
            } catch (ManzanaServiceContactSearchNullException $e) {
            } catch (ManzanaServiceException $e) {
            } catch (ConstraintDefinitionException $e) {
            } catch (NotAuthorizedException $e) {
            } catch (ServiceCircularReferenceException $e) {
            }
        }
        else{
            $manzanaParams['contact_id']     = $params['contact_id'];
            $manzanaParams['cardnumber']     = $params['cardnumber'];
        }
        $result = '';
        if(!empty($manzanaParams)) {
            if (!empty($params['firstname'])) {
                $manzanaParams['firstname'] = $params['firstname'];
            }
            if (!empty($params['lastname'])) {
                $manzanaParams['lastname'] = $params['lastname'];
            }
            if (!empty($params['middlename'])) {
                $manzanaParams['middlename'] = $params['middlename'];
            }
            if (!empty($params['mobilephone'])) {
                $manzanaParams['mobilephone'] = $params['mobilephone'];
            }
            if (!empty($params['emailaddress1'])) {
                $manzanaParams['emailaddress1'] = $params['emailaddress1'];
            }
            if (!empty($params['birthdate'])) {
                $manzanaParams['birthdate'] = $params['birthdate'];
            }
            if (!empty($params['gendercode'])) {
                $manzanaParams['gendercode'] = $params['gendercode'];
            }
            $bag = new ParameterBag(
                $manzanaParams
            );
            
            try {
                $rawResult = $this->execute(self::CONTRACT_CARD_ATTACH, $bag->getParameters());
        
                $result = ResultXmlFactory::getReferralCardAttachResultFromXml($this->serializer, $rawResult);
                if (!$result->isError()) {
                    $result = $result->getContactId();
                }
            } catch (AuthenticationException $e) {
            } catch (\Exception $e) {
                try {
                    $detail = $e->detail->details->description;
                } catch (\Throwable $e) {
                    $detail = 'none';
                }
                switch($detail){
                    case 'Card is completed':
                        break;
                    case 'Card is blocked':
                        break;
                    case 'Card is closed':
                        break;
                    case 'Card belongs to other referrer':
                        break;
                }
            }
        }
    
        return $result;
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
     *
     * @param User $user
     *
     * @return array
     * @throws ServiceNotFoundException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws ManzanaServiceException
     * @throws ServiceCircularReferenceException
     */
    public function getUserReferralList(User $user = null) : array
    {
        $contact_id = $this->getContactIdByCurUser($user);
        $referrals = [];
        if($contact_id > 0) {
            $bag = new ParameterBag(
                [
                    'contact_id'       => (string)$contact_id
                ]
            );
    
            try {
                $result = $this->execute(self::CONTRACT_CONTACT_REFERRAL_CARDS, $bag->getParameters());
                //echo '<pre>',var_dump($result),'</pre>';
                /** @var Referrals $res */
                $res = $this->serializer->deserialize($result, Referrals::class, 'xml');
                $referrals = $res->referrals->toArray();
            } catch (\Exception $e) {
                throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return $referrals;
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
