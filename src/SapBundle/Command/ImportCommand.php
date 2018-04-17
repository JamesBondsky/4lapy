<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\AppBundle\Service\LockerInterface;
use FourPaws\SapBundle\Pipeline\PipelineRegistry;
use FourPaws\SapBundle\Service\SapService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SapCommand
 *
 * @package FourPaws\SapBundle\Command
 */
class ImportCommand extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private const ARGUMENT_PIPELINE = 'pipeline';
    private const OPTION_FORCE = 'force';
    private const OPTION_FORCE_SHORTCUT = 'f';

    /**
     * @var PipelineRegistry
     */
    protected $pipelineRegistry;

    /**
     * @var SapService
     */
    protected $sapService;
    /**
     * @var LockerInterface
     */
    private $lockerService;

    /**
     * ImportCommand constructor.
     *
     * @param SapService $sapService
     * @param PipelineRegistry $pipelineRegistry
     * @param LockerInterface $lockerService
     *
     * @throws LogicException
     */
    public function __construct(SapService $sapService, PipelineRegistry $pipelineRegistry, LockerInterface $lockerService)
    {
        $this->pipelineRegistry = $pipelineRegistry;
        $this->sapService = $sapService;
        $this->lockerService = $lockerService;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this->setName('fourpaws:sap:import')
            ->setDescription('Sap exchange. Start exchange by type.')
            ->addArgument(
                self::ARGUMENT_PIPELINE,
                InputArgument::REQUIRED,
                \sprintf(
                    'Pipeline. %s',
                    \implode(
                        ', ',
                        $this->pipelineRegistry->getCollection()->getKeys()
                    )
                )
            )
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @return void
     *
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $available = $this->pipelineRegistry->getCollection()->getKeys();
        $pipeline = $input->getArgument(self::ARGUMENT_PIPELINE);
        $force = $input->getOption(self::OPTION_FORCE);

        if ($force) {
            $this->lockerService->unlock($pipeline);
        }

        if ($this->lockerService->isLocked($pipeline)) {
            throw new RuntimeException(
                \sprintf(
                    'Pipeline %s is locked',
                    $pipeline
                ));
        }

        if (!\in_array($pipeline, $available, true)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Wrong pipeline %s, available: %s',
                    $pipeline,
                    \implode(', ', $available)
                ));
        }

        $this->lockerService->lock($pipeline);

        try {
            $this->sapService->execute($pipeline);
            $this->log()->info(\sprintf('%s`s exchange is done.', $pipeline));
        } catch (\Throwable $e) {
            $this->log()->critical(\sprintf('Unknown error: %s, trace: %s', $e->getMessage(), $e->getTraceAsString()));
        }

        $this->lockerService->unlock($pipeline);
    }
}
