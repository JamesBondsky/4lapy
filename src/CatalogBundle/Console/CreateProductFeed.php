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
 * Class ExportDescriptionToCsv
 *
 * @package FourPaws\CatalogBundle\Console
 */
class CreateProductFeed extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * ImportDescriptionFromCsv constructor.
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
            ->setName('bitrix:product:description:export')
            ->setDescription('Export detail_text, composition, norms of use to csv file for active products without description')
            ->addArgument('path', InputArgument::REQUIRED, 'Full path to csv file');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws ObjectPropertyException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ArgumentException
     * @throws SystemException
     * @throws IblockNotFoundException
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $path = $input->getArgument('path');

        if ($path && $out = fopen($path, 'wb')) {
            $progress = new ProgressBar($output, 100);
            $progress->start();

            $products = $this->findProducts();

            $total = \count($products) - 1;

            foreach ($products as $k => $product) {
                if ($k % 50 === 0) {
                    $progress->setProgress($this->countPercent($total, $k));
                }

                \fputcsv($out, $product);
            }

            $progress->setProgress(100);

            $this->log()->debug(sprintf('Count: %s', $total));
        }
    }

    /**
     * @param int $total
     * @param int $current
     *
     * @return float
     */
    protected function countPercent(int $total, int $current): float
    {
        return \floor($current / ($total / 100));
    }

    /**
     * @return array
     *
     * @throws RuntimeException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     */
    protected function findProducts(): array
    {
        $filter = [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
            'DETAIL_TEXT' => false,
            'ACTIVE' => 'Y'
        ];

        $select = [
            'ID',
            'NAME',
            'XML_ID',
            'DETAIL_TEXT',
        ];

        $products = [
            [
                'XML_ID',
                'NAME',
                'NAME',
                'DETAIL_TEXT',
                'COMPOSITION',
                'NORMS_OF_USE',
                'ID',
            ]
        ];

        $res = (new Query(ElementTable::getEntity()))->setFilter($filter)->setSelect($select)->exec();

        /** @var array $el */
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($el = $res->fetch()) {
            $xmlId = CIBlockElement::GetList([], ['PROPERTY_CML2_LINK' => $el['ID']], false, ['nTopCount' => 1], ['XML_ID'])->Fetch();

            if (!$xmlId['XML_ID']) {
                $this->log()->error(\sprintf(
                    'Нет предложений у товара #%s',
                    $el['ID']
                ));

                continue;
            }

            $products[] = [
                $xmlId['XML_ID'],
                $el['NAME'],
                $el['NAME'],
                $el['DETAIL_TEXT'],
                '',
                '',
                $el['ID']
            ];
        }

        return $products;
    }
}

