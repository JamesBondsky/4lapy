<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Command;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyIndex\Manager;
use Bitrix\Main\Application;
use CIBlock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeactivateProducts extends Command
{
    private const PROGRESS_BAR_FORMAT = ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%';

    /**
     * @var int
     */
    private $iblockId;

    /**
     * {@inheritDoc}
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->iblockId = IblockUtils::getIblockId('catalog', 'products');
    }

    public function configure(): void
    {
        parent::configure();
        $this
            ->setName('fourpaws:deactivate:products')
            ->setDescription('Деактивирует большиство товаров, оставляя ограниченое количество товаров для секций.')
            ->addArgument('count', InputArgument::OPTIONAL, 'Сколько оставить товаров в секции', 10)
            ->addOption('force', 'f', InputOption::VALUE_NONE);
    }

    /**
     * {@inheritDoc}
     * @throws InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('force')) {
            $output->writeln('<error>Работает только с ключем --force</error>');
            return;
        }

        $count = (int)$input->getArgument('count');
        if ($count < 1) {
            $output->writeln('<error>Не возможно оставить менее одного элемента</error>');
            return;
        }

        $elements = $this->filterToDeactivateProducts($this->getProducts(), $count);
        $this->deactivateProducts(
            $elements,
            $output
        );
        $this->iblockElementsActions($elements, $output);

        $output->writeln(
            sprintf(
                '<fg=green>Осталось: %s товаров</> ',
                \count($this->filterToDeactivateProducts($this->getProducts(), 0))
            )
        );
    }

    /**
     * @param $elements
     * @param $count
     * @return array
     */
    protected function filterToDeactivateProducts(array $elements, int $count): array
    {
        $data = [];
        foreach ($elements as $element) {
            $data[$element['IBLOCK_SECTION_ID']][] = $element['ID'];
        }
        $data = array_filter($data, function ($array) use ($count) {
            return \count($array) > $count;
        });

        $data = array_map(function ($array) use ($count) {
            shuffle($array);
            return \array_slice($array, $count);
        }, $data);

        return array_merge(...$data) ?: [];
    }

    /**
     * @return array
     */
    protected function getProducts(): array
    {
        return ElementTable::query()
            ->addSelect('ID')
            ->addSelect('IBLOCK_SECTION_ID')
            ->addFilter('ACTIVE', 'Y')
            ->addFilter('IBLOCK_SECTION.ACTIVE', 'Y')
            ->addFilter('IBLOCK_ID', $this->iblockId)
            ->exec()
            ->fetchAll();
    }

    /**
     * @param                 $elements
     * @param OutputInterface $output
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    protected function deactivateProducts(array $elements, OutputInterface $output): void
    {
        if ($elements) {

            $connection = Application::getConnection();
            $connection->startTransaction();

            foreach (array_chunk($elements, 500) as $items) {
                $query = 'UPDATE b_iblock_element SET ACTIVE = \'N\' WHERE ID IN (' . implode(', ', $items) . ')';
                if (!$connection->query($query)->getResource()) {
                    $output->writeln('<error>Ошибка обновления товаров</error>');
                    $connection->rollbackTransaction();
                    return;
                }
            }
            $connection->commitTransaction();
            $output->writeln(sprintf('<fg=green>Деактивировано: %s товаров</> ', \count($elements)));
        }
    }

    protected function iblockElementsActions(array $elements, OutputInterface $output): void
    {
        if (!$elements) {
            return;
        }
        $output->writeln('<fg=green>Обновляем индекс элементов</> ');

        $progressBar = new ProgressBar($output, \count($elements));
        $progressBar->setFormat(static::PROGRESS_BAR_FORMAT);
        $progressBar->setRedrawFrequency(50);
        $progressBar->start();
        foreach ($elements as $id) {
            Manager::updateElementIndex($this->iblockId, $id);
            $progressBar->advance();
        }
        $progressBar->finish();

        $output->writeln('<fg=green>Сбрасываем тег инфоблока</> ');
        CIBlock::clearIblockTagCache($this->iblockId);
    }
}
