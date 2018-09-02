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
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Filesystem;

class AvailabilityReportService
{
    protected const RC_STORE_CODE = 'DC01';
    protected const CHUNK_SIZE    = 500;

    protected const STEP_ALL   = 0;
    protected const STEP_FIRST = 1;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * AvailabilityReportService constructor.
     * @param StoreService        $storeService
     * @param Filesystem          $filesystem
     * @param SerializerInterface $serializer
     */
    public function __construct(StoreService $storeService, Filesystem $filesystem, SerializerInterface $serializer)
    {
        $this->storeService = $storeService;
        $this->fileSystem = $filesystem;
        $this->serializer = $serializer;
    }

    /**
     * @param string   $path
     * @param int      $step
     * @param string[] $articles
     *
     * @return ReportResult
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function export(string $path, int $step, array $articles = []): ReportResult
    {
        if (\in_array($step, [
            static::STEP_ALL,
            static::STEP_FIRST,
        ], true)) {
            $this->fileSystem->dumpFile($path, implode(',', $this->getHeader()));
        }
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

        $countProcessed = 0;
        foreach ($productIds as $chunk) {
            $data = $this->findProducts($chunk);
            /** @todo сериализация */
//            $this->fileSystem->appendToFile(
//                $path,
//                $this->serializer->serialize($data, 'csv')
//            );
            $currentStep++;
            $countProcessed += \count($data);
        }

        return (new ReportResult())
            ->setCountProcessed($countProcessed)
            ->setCountTotal($countTotal)
            ->setProgress($currentStep / $stepCount);
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
     * @return string[]
     */
    protected function getHeader(): array
    {
        return [
            'Внешний код',
            'Название',
            'Картинки',
            'Описание',
            'Активен',
            'Дата создания',
            'Количество на РЦ',
            'Цена',
        ];
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
