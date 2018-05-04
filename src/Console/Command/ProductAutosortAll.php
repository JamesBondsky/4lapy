<?php

namespace FourPaws\Console\Command;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CIBlockElement;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\ProductAutoSort\ProductAutoSortService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProductAutosortAll
 *
 * @package FourPaws\Console\Command
 */
class ProductAutosortAll extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ProductAutoSortService
     */
    protected $productAutoSortService;
    protected $statProductTotal = 0;
    protected $statProductSectionsChanged = 0;
    protected $statProductSectionsSkipped = 0;
    protected $statProductUnsorted = 0;

    /**
     * ProductAutosortAll constructor.
     *
     * @throws ApplicationCreateException
     */
    public function __construct()
    {
        parent::__construct();

        $this->productAutoSortService = Application::getInstance()->getContainer()->get('product.autosort.service');
        $this->setLogger(LoggerFactory::create('ProductAutosortAll'));
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('productautosort:all')->setDescription('Perform autosorting on ALL PRODUCTS in catalog.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Category $unsortedCategory */
        $unsortedCategory = (new CategoryQuery())->withFilter(['=CODE' => 'unsorted'])->exec()->current();
        if (false == $unsortedCategory) {
            throw new RuntimeException('Не удаётся найти категорию для несортированных товаров. Кто-то удалил её?');
        }

        $cIBlockElement = new CIBlockElement();

        $this->log()->debug('Идёт определение категорий для всего каталога...');
        $productToCategories = $this->productAutoSortService->defineAllProductsCategories();
        $this->log()->debug('Готово.');

        $dbAllProducts =
            (new Query(ElementTable::getEntity()))->setFilter([
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG,
                    IblockCode::PRODUCTS),
            ])->setSelect([
                'IBLOCK_ID',
                'ID',
            ])->setOrder(['ID' => 'ASC'])->exec();
        $this->statProductTotal = $dbAllProducts->getSelectedRowsCount();

        $this->log()->info(sprintf('Всего товаров в каталоге: %d', $this->statProductTotal));

        $this->log()->debug('Идёт перепривязка товаров...');
        while ($product = $dbAllProducts->Fetch()) {
            //Если нет информации о привязке
            if (!isset($productToCategories[$product['ID']])
                || !is_array($productToCategories[$product['ID']])
                || count($productToCategories[$product['ID']]) == 0) {

                //Переместить товар в несортированные
                $cIBlockElement->SetElementSection($product['ID'], [$unsortedCategory->getId()]);
                $this->statProductUnsorted++;

                continue;
            }

            if ($cIBlockElement->SetElementSection($product['ID'], $productToCategories[$product['ID']], false, 0,
                $productToCategories[$product['ID']][0])) {
                $this->statProductSectionsChanged++;
            } else {
                $this->statProductSectionsSkipped++;
            }
        }

        $this->log()->debug('Готово.');
        $this->log()->info('Неотсортированных товаров: ' . $this->statProductUnsorted);
        $this->log()->info('Товаров перемещено: ' . $this->statProductSectionsChanged);
        $this->log()->info('Товаров без перемещений: ' . $this->statProductSectionsSkipped);

    }

    /**
     * @return LoggerInterface
     */
    protected function log()
    {
        return $this->logger;
    }
}
