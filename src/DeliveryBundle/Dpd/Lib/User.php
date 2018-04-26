<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Dpd\Lib;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use FourPaws\DeliveryBundle\Dpd\Lib\Service\Calculator;

if (!Loader::includeModule('ipol.dpd')) {
    class User
    {
    }

    return;
}

class User extends \Ipolh\DPD\API\User
{

    protected $services;

    protected static $classmap = array(
        'geography'   => '\\Ipolh\\DPD\\API\\Service\\Geography',
        'calculator'  => Calculator::class,
        'order'       => '\\Ipolh\\DPD\\API\\Service\\Order',
        'label-print' => '\\Ipolh\\DPD\\API\\Service\\LabelPrint',
        'tracking'    => '\\Ipolh\\DPD\\API\\Service\\Tracking',
    );

    /**
     * @param bool $defaultAccount
     *
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @return self
     */
    public static function getInstance($defaultAccount = false)
    {
        $defaultAccount = $defaultAccount ?: Option::get(IPOLH_DPD_MODULE, 'API_DEF_COUNTRY');
        $defaultAccount = $defaultAccount === 'RU' ? '' : $defaultAccount;

        $clientNumber   = Option::get(IPOLH_DPD_MODULE, trim('KLIENT_NUMBER_'. $defaultAccount, '_'));
        $clientKey      = Option::get(IPOLH_DPD_MODULE, trim('KLIENT_KEY_'. $defaultAccount, '_'));
        $testMode       = Option::get(IPOLH_DPD_MODULE, 'IS_TEST');
        $currency       = Option::get(IPOLH_DPD_MODULE, trim('KLIENT_CURRENCY_'. $defaultAccount, '_'), 'RUB');

        if (!static::$instance[$defaultAccount] instanceof User) {
            static::$instance[$defaultAccount] = new static(
                $clientNumber,
                $clientKey,
                $testMode,
                $currency
            );
        }

        return static::$instance[$defaultAccount];
    }

    public function getService($serviceName)
    {
        if (isset(static::$classmap[$serviceName])) {
            return $this->services[$serviceName] ?: $this->services[$serviceName] = new static::$classmap[$serviceName]($this);
        }

        throw new SystemException("Service {$serviceName} not found");
    }
}
