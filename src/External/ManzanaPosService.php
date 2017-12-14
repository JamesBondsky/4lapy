<?php

namespace FourPaws\External;

use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Interfaces\ManzanaServiceInterface;
use FourPaws\External\Manzana\Exception\AuthenticationException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Exception\ContactNotFoundException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\Manzana\Model\Card;
use FourPaws\External\Manzana\Model\Cards;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\Manzana\Model\Clients;
use FourPaws\External\Manzana\Model\Contact;
use FourPaws\External\Manzana\Model\Contacts;
use FourPaws\External\Manzana\Model\ParameterBag;
use FourPaws\External\Traits\ManzanaServiceTrait;
use Psr\Log\LoggerAwareInterface;

/**
 * Class ManzanaService
 *
 * @package FourPaws\External
 */
class ManzanaPosService implements LoggerAwareInterface, ManzanaServiceInterface
{
    use ManzanaServiceTrait;
    
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
            $arguments = [
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
            
            throw new ExecuteException(sprintf('Execute error: %s, detail: %s', $e->getMessage(), $detail),
                                       $e->getCode(),
                                       $e);
        }
        
        return $result;
    }
}
