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
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
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

    protected const CHUNK_SIZE = 500;

    /** @var StoreService */
    protected $storeService;

    /**
     * ProductsReport constructor.
     *
     * @param StoreService $storeService
     * @param string|null  $name
     *
     */
    public function __construct(StoreService $storeService, string $name = null)
    {
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

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
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

        if ($path && $out = fopen($path, $step <= 1 ? 'wb' : 'ab')) {
            $productIds = \array_chunk($this->getProductIds($articles, $step), static::CHUNK_SIZE);
            if ($step <= 1) {
                \fputcsv($out, [
                    'Внешний код',
                    'Название',
                    'Картинки',
                    'Описание',
                    'Активен',
                    'Дата создания',
                    'Количество на РЦ',
                    'Цена',
                ]);
            }

            foreach ($productIds as $chunk) {
                $products = $this->findProducts($chunk);
                foreach ($products as $product) {
                    \fputcsv($out, $product);
                }
            }
        } else {
            throw new \RuntimeException(\sprintf('failed to open %s', $path));
        }
    }

    /**
     * @param array    $articles
     * @param int|null $step
     *
     * @return int[]
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getProductIds(array $articles = [], int $step = 0): array
    {
        $query = ElementTable::query()
            ->setSelect(['ID'])
            ->setFilter([
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS)
            ]);

        if ($step) {
            $query
                ->setOffset(static::CHUNK_SIZE * ($step - 1))
                ->setLimit(static::CHUNK_SIZE);
        }

        if ($articles) {
            $query->addFilter('XML_ID', $articles);
        }
        $products = $query->exec();

        $result = [];
        while ($product = $products->fetch()) {
            $result[] = $product['ID'];
        }

        return $result;
    }

    /**
     * @param array $productIds
     *
     * @return array
     * @throws ApplicationCreateException
     * @throws NotFoundException
     */
    protected function findProducts(array $productIds): array
    {
        $offers = (new OfferQuery())
            ->withFilter(['ID' => $productIds])
            ->exec();

        $store = $this->storeService->getStoreByXmlId('DC01');

        $result = [];
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            $result[] = [
                'XML_ID'      => $offer->getXmlId(),
                'NAME'        => $offer->getName(),
                'IMAGE'       => !empty($offer->getImagesIds()) ? 'Y' : 'N',
                'DESCRIPTION' => $offer->getProduct()->getDetailText()->getText() ? 'Y' : 'N',
                'ACTIVE'      => $offer->isActive() ? 'Y' : 'N',
                'DATE_CREATE' => $offer->getDateCreate() ? $offer->getDateCreate()->format('Y-m-d H:i:s') : '',
                'STOCKS'      => $offer->getAllStocks()->filterByStore($store)->getTotalAmount(),
                'PRICE'       => $offer->getPrice(),
            ];
        }

        return $result;
    }
}

