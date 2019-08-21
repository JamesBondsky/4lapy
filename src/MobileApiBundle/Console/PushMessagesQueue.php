<?php

namespace FourPaws\MobileApiBundle\Console;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\AppBundle\Service\LockerInterface;
use FourPaws\CatalogBundle\Console\FeedFactory;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\MobileApiBundle\Services\PushEventService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class PushMessagesQueue
 * @package FourPaws\MobileApiBundle\Console
 */
class PushMessagesQueue extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private const OPTION_FORCE = 'force';
    private const OPTION_FORCE_SHORTCUT = 'f';

    /** @var PushEventService */
    private $pushEventService;

    /**
     * @var LockerInterface
     */
    private $lockerService;

    public function __construct(PushEventService $pushEventService, LockerInterface $lockerService)
    {
        $this->pushEventService = $pushEventService;
        $this->lockerService = $lockerService;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this
            ->setName('bitrix:mobileApi:push:queue')
            ->setDescription('Runs push messages queue')
            ->addOption(
                self::OPTION_FORCE,
                self::OPTION_FORCE_SHORTCUT,
                InputOption::VALUE_NONE,
                'Force - with unlock pipeline.'
            );
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws ArgumentException
     * @throws IOException
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $pipeline = 'mobilePushQueue';
        $force = $input->getOption(self::OPTION_FORCE);

        if ($force) {
            $this->lockerService->unlock($pipeline);
        }
        if ($this->lockerService->isLocked($pipeline)) {
            $this->log()->critical(\sprintf(
                'Pipeline %s is locked',
                $pipeline
            ));

            throw new RuntimeException(
                \sprintf(
                    'Pipeline %s is locked',
                    $pipeline
                ));
        }
        $this->lockerService->lock($pipeline);

        try {
//            $this->pushEventService->handleRowsWithFile(); // преобразование пользователей из прикрепленного файла в пользователей в поле
//            $this->log()->info('handleRowsWithFile done.');
//            $this->pushEventService->handleRowsWithoutFile(); // создание push-event`ов, снятие активности в hl-блоке "Push уведомления"
//            $this->log()->info('handleRowsWithoutFile done.');
//
//            // отправка push`ей
//            $this->pushEventService->execPushEventsForAndroid();
//            $this->log()->info('execPushEventsForAndroid done.');
            $this->pushEventService->execPushEventsForIos();
            $this->log()->info('execPushEventsForIos done.');

            $this->log()->info('push messages are sent.');
        } finally {
            $this->lockerService->unlock($pipeline);
        }

        // делаем задержку между отправками
        sleep(10);
        return FeedFactory::EXIT_CODE_END;
    }
}

