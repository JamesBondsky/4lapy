<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Data\Cache;
use FourPaws\AppBundle\Service\LockerInterface;
use FourPaws\SaleBundle\Service\PaymentService;
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
    private const OPTION_NO_BASKET_VALIDATION = 'nobasketvalidation';
    private const OPTION_NO_BASKET_VALIDATION_SHORTCUT = 'b';

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
     * @var PaymentService
     */
    private $paymentService;

    /**
     * ImportCommand constructor.
     *
     * @param SapService $sapService
     * @param PipelineRegistry $pipelineRegistry
     * @param LockerInterface $lockerService
     * @param PaymentService $paymentService
     *
     * @throws LogicException
     */
    public function __construct(SapService $sapService, PipelineRegistry $pipelineRegistry, LockerInterface $lockerService, PaymentService $paymentService)
    {
        $this->pipelineRegistry = $pipelineRegistry;
        $this->sapService = $sapService;
        $this->lockerService = $lockerService;
        $this->paymentService = $paymentService;

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
            )
            ->addOption(
                self::OPTION_NO_BASKET_VALIDATION,
                self::OPTION_NO_BASKET_VALIDATION_SHORTCUT,
                InputOption::VALUE_NONE,
                'No basket validation on fiscalization validation.'
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
        Cache::clearCache(true);
        $available = $this->pipelineRegistry->getCollection()->getKeys();
        $pipeline = $input->getArgument(self::ARGUMENT_PIPELINE);
        $force = $input->getOption(self::OPTION_FORCE);
        $isNoBasketValidation = $input->getOption(self::OPTION_NO_BASKET_VALIDATION);

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
            if ($isNoBasketValidation) {
                $this->paymentService->setCompareCartItemsOnValidateFiscalization(false);
            }
            $this->sapService->execute($pipeline);
            $this->log()->info(\sprintf('%s`s exchange is done.', $pipeline));
        } catch (\Throwable $e) {
            $this->log()->critical(\sprintf('Unknown error: %s, trace: %s', $e->getMessage(), $e->getTraceAsString()));
        }

        $this->lockerService->unlock($pipeline);
    }
}
