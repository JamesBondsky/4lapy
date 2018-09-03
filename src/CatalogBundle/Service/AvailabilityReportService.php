<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Dto\ProductReport\AvailabilityReport\Product;
use FourPaws\CatalogBundle\Dto\ProductReport\ReportResult;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;

/**
 * Class AvailabilityReportService
 * @package FourPaws\CatalogBundle\Service
 */
class AvailabilityReportService
{
    protected const RC_STORE_CODE = 'DC01';
    protected const CHUNK_SIZE    = 200;

    protected const STEP_ALL   = 0;
    protected const STEP_FIRST = 1;



    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * AvailabilityReportService constructor.
     * @param StoreService              $storeService
     * @param Filesystem                $filesystem
     * @param ArrayTransformerInterface $arrayTransformer
     * @param Serializer                $serializer
     */
    public function __construct(
        StoreService $storeService,
        Filesystem $filesystem,
        ArrayTransformerInterface $arrayTransformer,
        Serializer $serializer
    )
    {
        $this->serializer = $serializer;
        $this->storeService = $storeService;
        $this->fileSystem = $filesystem;
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @param string      $path
     * @param int         $step
     * @param string[]    $articles
     * @param string|null $encoding
     *
     * @return ReportResult
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function export(string $path, int $step, array $articles = [], string $encoding = null): ReportResult
    {
        $productIds = $this->getProductIds($articles);
        $countTotal = \count($productIds);
        $productIds = \array_chunk($productIds, static::CHUNK_SIZE);
        $stepCount = \count($productIds);

        if ($step !== static::STEP_ALL) {
            $currentStep = $step - 1;
            $productIds = [$productIds[$currentStep]];
        } else {
            $currentStep = static::STEP_ALL;
        }

        $append = $currentStep !== 0;
        $countProcessed = 0;
        foreach ($productIds as $chunk) {
            if (!$chunk) {
                continue;
            }

            $data = $this->findProducts($chunk);

            $result = [];
            foreach ($data as $product) {
                $result[] = $this->arrayTransformer->toArray($product);
            }

            $this->write($path, $this->serializer->encode($result, 'csv'), $append, $encoding);
            $currentStep++;
            $countProcessed += \count($data);
            $append = true;
        }

        return (new ReportResult())
            ->setCountProcessed($countProcessed)
            ->setCountTotal($countTotal)
            ->setProgress($currentStep / $stepCount);
    }

    /**
     * @param string      $path
     * @param string      $result
     * @param bool        $append
     * @param string|null $encoding
     */
    protected function write(string $path, string $result, bool $append, string $encoding = null): void
    {
        if (null !== $encoding && $encoding !== mb_internal_encoding()) {
            $result = mb_convert_encoding($result, $encoding);
        }

        if ($append) {
            // удаление заголовка
            $data = \explode(PHP_EOL, $result);
            array_shift($data);
            $result = \implode(PHP_EOL, $data);

            $this->fileSystem->appendToFile($path, $result);
        } else {
            $this->fileSystem->dumpFile($path, $result);
        }
    }

    /**
     * @param array $articles
     *
     * @return array
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getProductIds(array $articles = []): array
    {
        $query = ElementTable::query()
            ->setSelect(['ID'])
            ->setFilter([
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
            ]);

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
     * @return Product[]
     * @throws NotFoundException
     * @throws ApplicationCreateException
     */
    protected function findProducts(array $productIds): array
    {
        $store = $this->storeService->getStoreByXmlId(static::RC_STORE_CODE);

        $offers = (new OfferQuery())
            ->withFilter(['ID' => $productIds])
            ->exec();

        $result = [];
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            $result[] = (new Product())
                ->setXmlId($offer->getXmlId())
                ->setName($offer->getName())
                ->setImage(!empty($offer->getImagesIds()))
                ->setDescription((bool)$offer->getProduct()->getDetailText()->getText())
                ->setActive($offer->isActive())
                ->setDateCreate($offer->getDateCreate())
                ->setStocks($offer->getAllStocks()->filterByStore($store)->getTotalAmount())
                ->setPrice($offer->getPrice());
        }

        return $result;
    }
}
