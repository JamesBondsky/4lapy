<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Console;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\InvalidArgumentException as ProcessInvalidArgumentException;
use Symfony\Component\Process\Exception\LogicException as SymfonyLogicException;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Class FeedFactory
 *
 * @package FourPaws\CatalogBundle\Console
 */
class FeedFactory extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const ARG_PROFILE_ID = 'id';
    public const OPT_FEED_TYPE  = 'type';
    public const OPT_FEED_STEP  = 'step';

    public const FEED_TYPE_YANDEX_MARKET   = 'yandex-market';
    public const FEED_TYPE_GOOGLE_MERCHANT = 'google-merchant';

    public const EXIT_CODE_CONTINUE = 126;
    public const EXIT_CODE_END      = 127;

    /**
     * CreateProductFeed constructor.
     *
     * @param string|null $name
     *
     * @throws LogicException
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this
            ->setName('bitrix:feed:factory')
            ->setDescription('Run bitrix export task')
            ->addArgument(static::ARG_PROFILE_ID, InputArgument::REQUIRED, 'Bitrix feed id')
            ->addOption(
                static::OPT_FEED_TYPE,
                't',
                InputOption::VALUE_REQUIRED,
                'type of feed'
            );
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws ProcessInvalidArgumentException
     * @throws SymfonyLogicException
     * @throws ProcessRuntimeException
     * @throws ArgumentException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $id = $input->getArgument(static::ARG_PROFILE_ID);
        $type = $input->getOption(static::OPT_FEED_TYPE);
        $step = 0;
        $iterator = 1;

        if (!$id) {
            throw new ArgumentException('Profile id is not defined');
        }

        while (true) {
            $process = new Process($this->getFeedProcessName($id, $type, $step));
            $process->setTimeout(600);
            $process->run();
            dump($process->getOutput());

            if ($process->getExitCode() !== self::EXIT_CODE_CONTINUE) {
                break;
            }

            $step = 1;
            $this->log()->info(\sprintf('Step #%d was finished', $iterator));
            $iterator++;
        }

        $this->log()
            ->info(\sprintf('Task #%s (%s) finished', $id, $type));
    }

    /**
     * @param int    $id
     * @param string $type
     * @param int    $step
     *
     * @return string
     *
     * @throws ArgumentException
     */
    public function getFeedProcessName(int $id, string $type, $step = 0): string
    {
        $php = (new PhpExecutableFinder())->find();

        switch ($type) {
            case self::FEED_TYPE_YANDEX_MARKET:
                $command = 'bitrix:feed:create:yandex';
                break;
            case self::FEED_TYPE_GOOGLE_MERCHANT:
                $command = 'bitrix:feed:create:google';
                break;
            default:
                throw new ArgumentException(\sprintf(
                    'Feed type %s is not available/',
                    $type
                ));
        }

        return \sprintf(
            '%s %s/bin/symfony_console %s %s --%s %s --%s %d',
            $php,
            \getcwd(),
            $command,
            $id,
            self::OPT_FEED_TYPE,
            $type,
            self::OPT_FEED_STEP,
            $step
        );
    }
}

