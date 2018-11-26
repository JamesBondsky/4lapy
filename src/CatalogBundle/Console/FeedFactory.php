<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Console;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
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
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

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
    public const OPT_FEED_STOCK_ID  = 'stock';

    public const FEED_TYPE_YANDEX_MARKET   = 'yandex-market';
    public const FEED_TYPE_GOOGLE_MERCHANT = 'google-merchant';
    public const FEED_TYPE_RETAIL_ROCKET   = 'retail-rocket';

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
            )
            ->addOption(
                static::OPT_FEED_STOCK_ID,
                'stock',
                InputOption::VALUE_REQUIRED,
                'stock id'
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
        $stockID = $input->getOption(static::OPT_FEED_STOCK_ID);
        $step = 0;
        $iterator = 1;

        $this->log()->info(
            \sprintf(
                'Feed factory was started: profile %d, feed %s, date %s, %s',
                $id,
                $type,
                (new \DateTime())->format('d-m-Y H:i:s'),
                !empty($stockID) ? 'with stock id ' . $stockID : ''
            )
        );

        if (!$id) {
            throw new ArgumentException('Profile id is not defined');
        }

        try {
            while (true) {
                $process = new Process($this->getFeedProcessName($id, $type, $step, $stockID));
                $process->setTimeout(600);
                $process->run();

                dump($process->getOutput());

                if ($process->getExitCode() === self::EXIT_CODE_END) {
                    break;
                }

                if ($process->getExitCode() !== self::EXIT_CODE_CONTINUE) {
                    throw new ProcessFailedException($process);
                }

                $step = 1;
                $this->log()->info(\sprintf('Step #%d was finished', $iterator));
                $iterator++;
            }
        } catch (Throwable $e) {
            $this->log()
                 ->critical(
                     \sprintf(
                         'Feed creation critical error: %s[%s] in %s:%s',
                         $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine()
                     )
                 );
        }

        $this->log()
             ->info(\sprintf('Task #%s (%s) finished', $id, $type));
    }

    /**
     * @param int    $id
     * @param string $type
     * @param int    $step
     * @param string $stockID
     *
     * @return string
     *
     * @throws ArgumentException
     */
    public function getFeedProcessName(int $id, string $type, $step = 0, $stockID = null): string
    {
        $php = (new PhpExecutableFinder())->find();

        switch ($type) {
            case self::FEED_TYPE_YANDEX_MARKET:
                $command = 'bitrix:feed:create:yandex';
                break;
            case self::FEED_TYPE_GOOGLE_MERCHANT:
                $command = 'bitrix:feed:create:google';
                break;
            case self::FEED_TYPE_RETAIL_ROCKET:
                $command = 'bitrix:feed:create:retailrocket';
                break;
            default:
                throw new ArgumentException(\sprintf(
                    'Feed type %s is not available/',
                    $type
                ));
        }

        return \sprintf(
            '%s %s/bin/symfony_console %s %s --%s %d %s',
            $php,
            Application::getInstance()->getRootDir(),
            $command,
            $id,
            self::OPT_FEED_STEP,
            $step,
            !empty($stockID) ? '--' . self::OPT_FEED_STOCK_ID . ' ' . $stockID : ''
        );
    }
}

