<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Console;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
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
 * Class CreateProductFeed
 *
 * @package FourPaws\CatalogBundle\Console
 */
class CreateProductFeed extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const ARG_PROFILE_ID          = 'id';
    public const OPT_FEED_TYPE           = 'type';
    public const FEED_TYPE_YANDEX_MARKET = 'yandex-market';

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
            ->setName('bitrix:product:feed:create')
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $id = $input->getArgument(static::ARG_PROFILE_ID);
        $type = $input->getOption(static::OPT_FEED_TYPE);
        if (!$id) {
            throw new RuntimeException('Profile id not defined');
        }

        if (!\CCatalogExport::GetByID($id)) {
            throw new RuntimeException(\sprintf('Profile with id #%s not found', $id));
        }

        if (!\CCatalogExport::PreGenerateExport($id)) {
            $this->log()->error(\sprintf('Failed to generate feed for profile #%s', $id));
        } else {
            $this->runAfterExport($type);
        }

        $this->log()->info(\sprintf('Task #%s (%s) finished', $id, $type));
    }

    /**
     * @param string $type
     * @throws ApplicationCreateException
     */
    protected function runAfterExport(string $type)
    {
        switch ($type) {
            case static::FEED_TYPE_YANDEX_MARKET:
                Application::getInstance()->getContainer()->get('yandex_market.service')->deleteAllPrices();
                break;
        }
    }
}

