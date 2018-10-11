<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\SaleBundle\Service\Reports\RROrderReportService;
use FourPaws\StoreBundle\Service\StoreService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Class ProductsReport
 *
 * @package FourPaws\CatalogBundle\Console
 */
class RROrdersReport extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const OPT_PATH     = 'path';
    protected const OPT_FROM     = 'from';
    protected const OPT_STEP     = 'step';

    protected const DATE_FORMAT = 'Y-m-d';

    /** @var StoreService */
    protected $storeService;

    /**
     * @var RROrderReportService
     */
    protected $rrOrderReportService;

    /**
     * ProductsReport constructor.
     *
     * @param StoreService         $storeService
     * @param RROrderReportService $rrOrderReportService
     * @param string|null          $name
     *
     * @throws LogicException
     */
    public function __construct(
        StoreService $storeService,
        RROrderReportService $rrOrderReportService,
        string $name = null
    )
    {
        $this->rrOrderReportService = $rrOrderReportService;
        $this->storeService = $storeService;
        parent::__construct($name);
    }

    /**
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function configure(): void
    {
        $this
            ->setName('fourpaws:sale:orders:retailrocket:report')
            ->setDescription('Generate order report for retail rocket')
            ->addOption(
                static::OPT_PATH,
                'p',
                InputOption::VALUE_REQUIRED,
                'Full path to csv file'
            )
            ->addOption(
                static::OPT_FROM,
                'd',
                InputOption::VALUE_OPTIONAL,
                \sprintf('Date from (%s)', static::DATE_FORMAT)
            )
            ->addOption(
                static::OPT_STEP,
                's',
                InputOption::VALUE_OPTIONAL,
                'Current step'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws IOException
     * @throws RuntimeException
     * @throws UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $path = $input->getOption(static::OPT_PATH);
        if (null === $path) {
            throw new \InvalidArgumentException('Path is not defined');
        }

        $step = (int)$input->getOption(static::OPT_STEP);
        if ($dateOption = $input->getOption(static::OPT_FROM)) {
            if (!$from = date_create_from_format(static::DATE_FORMAT, $dateOption)) {
                throw new \InvalidArgumentException(\sprintf('Date must be in %s format', static::DATE_FORMAT));
            }
        } else {
            $from = (new \DateTime())->modify('-1 year');
        }

        $this->rrOrderReportService->export($path, $step, $from);
    }
}

