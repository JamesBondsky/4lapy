<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Console;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\CatalogBundle\Service\YandexFeedService;
use FourPaws\CatalogBundle\Translate\BitrixExportConfigTranslator;
use FourPaws\External\Exception\YandexMarketApiException;
use FourPaws\External\YandexMarketService;
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
 * Class CreateYandexProductFeed
 *
 * @package FourPaws\CatalogBundle\Console
 */
class CreateYandexProductFeed extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const ARG_PROFILE_ID          = 'id';
    public const OPT_FEED_TYPE           = 'type';
    public const FEED_TYPE_YANDEX_MARKET = 'yandex-market';
    public const OPT_FEED_STEP           = 'step';
    /**
     * @var YandexFeedService
     */
    private $feedService;
    /**
     * @var BitrixExportConfigTranslator
     */
    private $translator;
    /**
     * @var YandexMarketService
     */
    private $yandexMarketService;

    /**
     * CreateProductFeed constructor.
     *
     *
     * @param YandexFeedService            $feedService
     * @param BitrixExportConfigTranslator $translator
     * @param YandexMarketService          $yandexMarketService
     *
     * @throws LogicException
     */
    public function __construct(YandexFeedService $feedService, BitrixExportConfigTranslator $translator, YandexMarketService $yandexMarketService)
    {
        $this->feedService = $feedService;
        $this->translator = $translator;
        $this->yandexMarketService = $yandexMarketService;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this
            ->setName('bitrix:feed:create:yandex')
            ->setDescription('Run bitrix export task')
            ->addArgument(static::ARG_PROFILE_ID, InputArgument::REQUIRED, 'Bitrix feed id')
            ->addOption(
                static::OPT_FEED_TYPE,
                't',
                InputOption::VALUE_REQUIRED,
                'type of feed')
            ->addOption(
                static::OPT_FEED_STEP,
                's',
                InputOption::VALUE_REQUIRED,
                'Step',
                0
            );
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws IOException
     * @throws YandexMarketApiException
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument(static::ARG_PROFILE_ID);
        $type = $input->getOption(static::OPT_FEED_TYPE);
        $step = $input->getOption(static::OPT_FEED_STEP);

        if (!$id) {
            throw new RuntimeException('Profile id not defined');
        }

        $configuration = $this->translator->translate($this->translator->getProfileData($id));

        if ($this->feedService->process($configuration, $step)) {
            $this->log()
                ->info('Step cleared');

            return FeedFactory::EXIT_CODE_CONTINUE;
        }

        $this->log()
            ->info('Feed was create');
        $this->runAfterExport($type);

        return FeedFactory::EXIT_CODE_END;
    }

    /**
     * @todo move to service
     *
     * @param string $type
     *
     * @throws YandexMarketApiException
     */
    protected function runAfterExport(string $type)
    {
        switch ($type) {
            case static::FEED_TYPE_YANDEX_MARKET:
                $this->yandexMarketService->deleteAllPrices();
                break;
        }
    }
}

