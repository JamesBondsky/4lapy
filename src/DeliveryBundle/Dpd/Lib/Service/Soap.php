<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Dpd\Lib\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Loader;
use Ipolh\DPD\API\User;
use Psr\Log\LoggerAwareInterface;

if (!Loader::includeModule('ipol.dpd')) {
    class Soap
    {
    }

    return;
}


class Soap extends \Ipolh\DPD\API\Client\Soap implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public function __construct(string $wsdl, User $user, array $options = array())
    {
        parent::__construct($wsdl, $user, $options);
        $this->withLogName(str_replace('\\', '_', static::class));
        if ($this->initError) {
            $this->log()->error(sprintf('failed to connect to dpd wsdl: %s', $this->initError), [
                'url' => $wsdl,
                'options' => $options
            ]);
        }
    }

    public function invoke($method, array $args = array(), $wrap = 'request', $keys = false)
    {
        $result = parent::invoke($method, $args, $wrap, $keys);

        if (!$result) {
            /** @var \SoapFault $soapFault */
            if (property_exists($this, '__soap_fault') && $soapFault = $this->__soap_fault) {
                $this->log()->error(
                    sprintf(
                        'error while calling method %s: %s %s',
                        $method,
                        $soapFault->getCode(),
                        $soapFault->getMessage()),
                    [
                        'arguments' => $args
                    ]
                );
            }
        }

        return $result;
    }
}
