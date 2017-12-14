<?php

namespace FourPaws\Test\Tests;

use Adv\Bitrixtools\Tools\MiscUtils;
use FourPaws\Test\AppManager\ApplicationManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class TestBase extends PHPUnit_Framework_TestCase implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ApplicationManager
     */
    public static $applicationManager;

    /**
     * @var bool Обязательно false, т.к. Битрикс по-другому тестить нельзя и его глобальные переменные не могут быть
     *     корректно восстановлены
     */
    protected $backupGlobals = false;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        self::$applicationManager = new ApplicationManager();
        $this->setLogger(
            (new Logger(MiscUtils::getClassName($this)))->pushHandler(new StreamHandler(STDOUT, Logger::DEBUG))
        );

        //TODO Добавить сброс всего кеша  перед началом тестирования.
    }

    /**
     * @return LoggerInterface
     */
    public function log(): LoggerInterface
    {
        return $this->logger;
    }
}
