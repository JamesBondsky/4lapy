<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Console;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\CatalogBundle\Service\ExpertSenderFeedService;
use FourPaws\CatalogBundle\Translate\BitrixExportConfigTranslator;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class CreateExpertSenderProductFeed
 *
 * @todo
 *
 * @package FourPaws\CatalogBundle\Console
 */
class CreateExpertSenderProductFeed extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const ARG_PROFILE_ID          = 'id';
    public const OPT_FEED_STEP           = 'step';
    public const OPT_FEED_STOCK_ID       = 'stock';
    /**
     * @var ExpertSenderFeedService
     */
    private $feedService;
    /**
     * @var BitrixExportConfigTranslator
     */
    private $translator;

    /**
     * CreateProductFeed constructor.
     *
     * @param ExpertSenderFeedService            $feedService
     * @param BitrixExportConfigTranslator $translator
     *
     * @throws LogicException
     */
    public function __construct(ExpertSenderFeedService $feedService, BitrixExportConfigTranslator $translator)
    {
        $this->feedService = $feedService;
        $this->translator = $translator;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this
            ->setName('bitrix:feed:create:expertsender')
            ->setDescription('Run bitrix export task - expertsender feed')
            ->addArgument(static::ARG_PROFILE_ID, InputArgument::REQUIRED, 'Bitrix feed id')
            ->addOption(
                static::OPT_FEED_STEP,
                's',
                InputOption::VALUE_REQUIRED,
                'Step',
                0
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
        $id = $input->getArgument(static::ARG_PROFILE_ID);
        $step = $input->getOption(static::OPT_FEED_STEP);
        $stockID = $input->getOption(static::OPT_FEED_STOCK_ID);

        if (!$id) {
            throw new RuntimeException('Profile id not defined');
        }

        $configuration = $this->translator->translate($this->translator->getProfileData($id));

        try {
            if (!empty($stockID)) {
                $configuration->setExportFile('/bitrix/catalog_export/expertsender_export_feed_' . $stockID . '.xml');
            }
            if ($this->feedService->process($configuration, $step, $stockID)) {
                $this->log()->info('Step cleared');

                return FeedFactory::EXIT_CODE_CONTINUE;
            }
        } catch (\Throwable $e) {
            $this->log()->error(
                \sprintf(
                    'Export error: code %s, message %s in %s:%d',
                    $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine()
                )
            );
        }

        $this->log()->info('Feed was create');

        return FeedFactory::EXIT_CODE_END;
    }
}

