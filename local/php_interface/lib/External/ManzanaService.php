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
    protected $_client;
    
    /**
     * @var \FourPaws\Health\HealthService
     */
    protected $_healthService;
    
    /**
     * ManzanaService constructor.
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $this->_healthService = Application::getInstance()->getContainer()->get('health.service');
        /**
         * @todo move into parameters
         */
        $this->_client = new SoapClient((new Factory())->create(new Client(), ''), $this->_healthService);
        
        $this->setLogger(LoggerFactory::create('manzana'));
    }
    
    /**
     * @param string $cardNumber
     *
     * @return bool
     *
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    public function isCardValidByCardNumber(string $cardNumber) : bool
    {
        $parameters = [
            [
                'Name'  => 'cardnumber',
                'Value' => $cardNumber,
            ],
        ];
        
        $result = $this->_execute(self::CONTRACT_CARD_VALIDATE, $parameters);
        
        return (bool)$result->cardvalidateresult->isvalid->textContent();
    }
    
    /**
     * @param string $contract
     * @param array  $parameters
     *
     * @return \SimpleXMLElement
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    protected function _execute(string $contract, array $parameters) : \SimpleXMLElement
    {
        try {
            $result = $this->_client->execute($contract, $parameters);
        } catch (ManzanaException $e) {
            $this->logger->error(sprintf('Manzana execution error: error %s, code $s',
                                         $e->getMessage(),
                                         $e->getCode()));
            
            throw new ManzanaServiceException($e->getMessage(), $e->getCode(), $e);
        }
        
        return $result;
    }
}
