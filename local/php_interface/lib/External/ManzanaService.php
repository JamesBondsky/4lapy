<?php

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Client\SoapClient;
use FourPaws\External\Manzana\Exception\ManzanaException;
use GuzzleHttp\Client;
use Meng\AsyncSoap\Guzzle\Factory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class ManzanaService
 *
 * @package FourPaws\External
 */
class ManzanaService implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    const CONTRACT_CARD_VALIDATE = 'card_validate';
    
    /**
     * @var \FourPaws\External\Manzana\Client\SoapClient
     */
    protected $client;
    
    /**
     * @var \FourPaws\Health\HealthService
     */
    protected $healthService;
    
    /**
     * ManzanaService constructor.
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $container = Application::getInstance()->getContainer();
        $wdsl      = $container->getParameter('manzana')['wdsl'];
        
        $this->healthService = $container->get('health.service');
        
        $this->client = new SoapClient((new Factory())->create(new Client(), $wdsl), $this->healthService);
        
        $this->setLogger(LoggerFactory::create('manzana'));
    }
    
    /**
     * Отправка телефона
     *
     * - после верификации номера телефона
     * - заказ в один клик
     * - регистрация бонусной карты в ЛК магазина
     */
    public function sendPhone(string $phone)
    {
    
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
     * @param array $data
     */
    public function updateContact(array $data)
    {
    
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
     * @return array
     */
    public function getUserDataByPhone(string $phone) : array
    {
    
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
        $parameters = [
            [
                'Name'  => 'cardnumber',
                'Value' => $cardNumber,
            ],
        ];
        
        $result = $this->execute(self::CONTRACT_CARD_VALIDATE, $parameters);
        
        return $result->cardid->__toString() !== '';
    }
    
    /**
     * Получение данных о держателе бонусной карты
     *
     * @param string $cardNumber
     *
     * -
     * - ЛК магазина, просмотр истории по карте
     */
    public function searchCardByNumber(string $cardNumber)
    {
    
    }
    
    /**
     * Получение контакта по Contact_ID
     *
     * @param $contactId
     */
    public function getContactByContactId($contactId) {
    
    }
    
    /**
     * @param string $contract
     * @param array  $parameters
     *
     * @return \SimpleXMLElement
     * @throws ManzanaServiceException
     */
    protected function execute(string $contract, array $parameters) : \SimpleXMLElement
    {
        try {
            $result = $this->client->execute($contract, $parameters);
        } catch (ManzanaException $e) {
            $this->logger->error(sprintf('Manzana execution error: error %s, code $s',
                                         $e->getMessage(),
                                         $e->getCode()));
            
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }
        
        return $result;
    }
    
    public function getUserByPhoneNumber()
    {
    
    }
}
