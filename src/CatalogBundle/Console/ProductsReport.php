<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Console;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\CatalogBundle\Service\AvailabilityReportService;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProductsReport
 *
 * @package FourPaws\CatalogBundle\Console
 */
class ProductsReport extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const OPT_PATH     = 'path';
    protected const OPT_ARTICLES = 'articles';
    protected const OPT_STEP     = 'step';

    protected const CHUNK_SIZE = 2000;

    /** @var StoreService */
    protected $storeService;

    /**
     * @var AvailabilityReportService
     */
    protected $availabilityReportService;

    /**
     * ProductsReport constructor.
     *
     * @param StoreService              $storeService
     * @param AvailabilityReportService $availabilityReportService
     * @param string|null               $name
     */
    public function __construct(
        StoreService $storeService,
        AvailabilityReportService $availabilityReportService,
        string $name = null
    )
    {
        $this->availabilityReportService = $availabilityReportService;
        $this->storeService = $storeService;
        parent::__construct($name);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this
            ->setName('bitrix:product:report')
            ->setDescription('Export product availability to csv')
            ->addOption(
                static::OPT_PATH,
                'p',
                InputOption::VALUE_REQUIRED,
                'Full path to csv file'
            )
            ->addOption(
                static::OPT_ARTICLES,
                'a',
                InputOption::VALUE_OPTIONAL,
                'List of articles'
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
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $path = $input->getOption(static::OPT_PATH);
        $step = (int)$input->getOption(static::OPT_STEP);
        if ($articlesOption = $input->getOption(static::OPT_ARTICLES)) {
            $articles = explode(',', $articlesOption);
        } else {
            $articles = [];
        }

        $this->availabilityReportService->export($path, $step, $articles);
    }
}

