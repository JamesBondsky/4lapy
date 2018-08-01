<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Console;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CIBlockElement;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateProductFeed
 *
 * @package FourPaws\CatalogBundle\Console
 */
class CreateProductFeed extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

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
            ->addArgument('id', InputArgument::REQUIRED, 'Bitrix feed id');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws SystemException
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $id = $input->getArgument('id');
        if (!$id) {
            throw new RuntimeException('Profile id not defined');
        }

        if (! \CCatalogExport::GetByID($id)) {
            throw new RuntimeException(\sprintf('Profile with id #%s not found', $id));
        }

        if (!\CCatalogExport::PreGenerateExport($id)) {
            $this->log()->error(\sprintf('Failed to generate feed for profile #%s', $id));
        }
        $this->log()->info('Task finished');
    }
}

