<?php

namespace FourPaws\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;
use Throwable;

class ConsoleApp
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var string
     */
    private $documentRoot;

    public function __construct(string $documentRoot)
    {
        $this->documentRoot = $documentRoot;
    }

    public function run()
    {
        try {

            $this->init();
            $this->launchSymfonyConsoleApp();
            $this->finish();

        } catch (Throwable $exception) {

            echo sprintf(
                "[%s] %s (%s)\n%s\n",
                get_class($exception),
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getTraceAsString()
            );

            //Non-zero because error
            die(1);

        }
    }

    private function init()
    {
        /**
         * TODO Вынести подключение Битрикса в пакет adv/bitrix-tools ?
         */
        if (php_sapi_name() != 'cli') {
            die('Can not run in this mode. Bye!');
        }

        defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', 'Y');
        defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);
        defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
        defined('PUBLIC_AJAX_MODE') || define('PUBLIC_AJAX_MODE', true);

        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            $_SERVER['DOCUMENT_ROOT'] = $this->documentRoot;
        }

        $GLOBALS['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

        /** @noinspection PhpIncludeInspection */
        require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

        set_time_limit(0);
        // ini_set('memory_limit', '2G');
        error_reporting(E_ERROR);
    }

    private function launchSymfonyConsoleApp()
    {
        $this->application = new Application();
        $this->application->setName('4lapy console interface');
        $this->application->setVersion('1.0.0');
        $this->registerCommands();
        $this->application->run();
    }

    private function finish()
    {
        //TODO Возможно, требуется оптимизировать, чтобы Битрикс не делал чего-то лишнего в консоли
        /** @noinspection PhpIncludeInspection */
        require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
    }

    /**
     * Регистрирует все команды из namespace \Adv\Console\Command
     */
    private function registerCommands()
    {
        $files = new Finder();
        $files->files()->in(__DIR__ . '/Command');

        foreach ($files as $file) {

            $classPath = str_replace(
                [
                    \dirname(__DIR__),
                    '.php',
                ],
                '',
                $file->getRealPath()
            );


            $command = "\\FourPaws" . str_replace('/', '\\', $classPath);

            $this->application->add(new $command);
        }
    }
}
