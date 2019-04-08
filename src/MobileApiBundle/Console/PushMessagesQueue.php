<?php

namespace FourPaws\MobileApiBundle\Console;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\CatalogBundle\Console\FeedFactory;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\MobileApiBundle\Services\PushEventService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class CreateDostavistaActionFeed
 *
 * @todo
 *
 * @package FourPaws\CatalogBundle\Console
 */
class PushMessagesQueue extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** @var PushEventService */
    private $pushEventService;

    public function __construct(PushEventService $pushEventService)
    {
        $this->pushEventService = $pushEventService;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this
            ->setName('bitrix:mobileApi:push:queue')
            ->setDescription('Runs push messages queue');
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
        $this->pushEventService->handleRowsWithFile();
        $this->pushEventService->handleRowsWithoutFile();
        $this->pushEventService->execPushEventsForAndroid();
        $this->pushEventService->execPushEventsForIos();
        // делаем задержку между отправками
        sleep(10);
        return FeedFactory::EXIT_CODE_END;
    }
}

