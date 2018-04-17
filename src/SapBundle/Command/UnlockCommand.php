<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\AppBundle\Service\LockerInterface;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UnlockCommand
 *
 * @package FourPaws\SapBundle\Command
 */
class UnlockCommand extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private const ARGUMENT_PIPELINE = 'pipeline';

    /**
     * @var LockerInterface
     */
    private $lockerService;

    /**
     * UnlockCommand constructor.
     *
     * @param LockerInterface $lockerService
     *
     * @throws LogicException
     */
    public function __construct(LockerInterface $lockerService)
    {
        $this->lockerService = $lockerService;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this->setName('fourpaws:sap:unlock')
            ->setDescription('Unlock command.')
            ->addArgument(
                self::ARGUMENT_PIPELINE,
                InputArgument::REQUIRED,
                'Pipeline to unlock'
            );
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     *
     * @return void
     *
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $pipeline = $input->getArgument(self::ARGUMENT_PIPELINE);

        $this->lockerService->unlock($pipeline);

        $this->log()->info(\sprintf('%s is unlocked.', $pipeline));
    }
}
