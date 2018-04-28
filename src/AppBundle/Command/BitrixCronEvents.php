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
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BitrixCronEvents
 *
 * @package FourPaws\AppBundle\Command
 */
class BitrixCronEvents extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this->setName('bitrix:cronevents')->setDescription('Start bitrix events');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @see hack in /bin/symfony_console.php
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws RuntimeException
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        CEvent::CheckEvents();

        try {
            if (Loader::includeModule('sender')) {
                MailingManager::checkPeriod(false);
                MailingManager::checkSend();
            }
        } catch (\Exception $e) {
            $this->log()->error($e->getMessage());
        }
    }
}
