<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Loader;
use Bitrix\Sender\MailingManager;
use CEvent;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BitrixCronEvents extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public function configure()
    {
        $this->setName('bitrix:cronevents')->setDescription('Start bitrix events');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \RuntimeException
     * @return null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        \define('BX_CRONTAB', true);

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @ignore_user_abort(true);

        CEvent::CheckEvents();

        try {
            if (Loader::includeModule('sender')) {
                MailingManager::checkPeriod(false);
                MailingManager::checkSend();
            }
        } catch (\Exception $e) {
            $this->log()->error($e->getMessage());
        }

        /** @noinspection PhpIncludeInspection */
        require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/tools/backup.php';


        return null;
    }
}
