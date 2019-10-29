<?php

namespace FourPaws\SapBundle\Api;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareInterface;

class SapStatusSender implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    
    /**
     * @var string $apiUrl
     */
    private $apiUrl = 'http://95.181.195.45:8000/zset_status';
    
    private $orderNumber;
    private $orderStatus;
    private $guzzleClient;
    
    public const SAP_SUCCESS = 'S';
    public const SAP_WAIT    = 'W';
    public const SAP_ERROR   = 'E';
    
    public function __construct($orderNumber, $orderStatus)
    {
        $this->guzzleClient = new Client();
        
        $this->orderNumber = $orderNumber;
        $this->orderStatus = $orderStatus;
        
    }
    
    public function send()
    {
        $response = $this->guzzleClient->request('POST', $this->apiUrl, ['json' => ['WebID' => $this->orderNumber, 'StatusID' => $this->orderStatus]]);
        
        if ($response->getStatusCode() == 200) {
            $body = \json_decode($response->getBody());
            
            switch ($body->MSGTYPE) {
                case self::SAP_SUCCESS:
                    $this->log()
                        ->info(
                            \sprintf(
                                'Заказ номер %s успешно отменен.',
                                $this->orderNumber
                            )
                        );
                    break;
                case self::SAP_WAIT:
                    $this->log()
                        ->info(
                            \sprintf(
                                'Автоматически установить заказ номер %s нельзя, SAP инициировал ручную отмену.',
                                $this->orderNumber
                            )
                        );
                    break;
                case self::SAP_ERROR:
                    $this->log()
                        ->info(
                            \sprintf(
                                'Отмена заказа номер %s завершилась неудачей =( Код ошибки: %s . Текстовое описание ошибки %s .',
                                $this->orderNumber,
                                $body->MSGCODE,
                                $body->MSGTEXT
                            )
                        );
                    return false;
                    break;
            }
            
            return true;
        }
        
        return false;
    }
}
