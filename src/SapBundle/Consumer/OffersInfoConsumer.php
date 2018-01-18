<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Dto\In\Offers\Materials;
use FourPaws\SapBundle\Service\Materials\OfferService;
use FourPaws\SapBundle\Service\Materials\ProductService;
use FourPaws\SapBundle\Service\ReferenceService;
use Psr\Log\LoggerAwareInterface;

class OffersInfoConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ReferenceService
     */
    private $referenceService;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @var ProductService
     */
    private $productService;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        ReferenceService $referenceService,
        OfferService $offerService,
        ProductService $productService
    ) {
        $this->referenceService = $referenceService;
        $this->offerService = $offerService;
        $this->productService = $productService;
        $this->connection = Application::getConnection();
    }

    /**
     * @param Materials $offersInfo
     *
     * @throws \Bitrix\Main\DB\SqlQueryException
     * @return bool
     */
    public function consume($offersInfo): bool
    {
        if (!$this->support($offersInfo)) {
            return false;
        }
        $result = true;

        foreach ($offersInfo->getMaterials() as $material) {
            $this->connection->startTransaction();
            try {
                /**
                 * Создаем недостающие справочные даныне
                 */
                $this->referenceService->fillFromMaterial($material);


                $offer = $this->offerService->findByMaterial($material) ?: new Offer();
                $this->offerService->fillFromMaterial($offer, $material);
                dump($offer);

                $product = $this->productService->findByMaterial($material) ?: new Product();
                $this->productService->fillProduct($product, $material);

                dump($product);
//                $this->offerService->createFromMaterial($material);
            } catch (\Exception $exception) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $this->log()->error($exception->getMessage(), $exception->getTrace());
                $result = false;
            }
            $this->connection->rollbackTransaction();
        }

        return $result;
    }

    protected function createReferenceData(Material $material)
    {
        $this->referenceService->fillFromMaterial($material);
    }

    protected function createOrUpdateOffer(Material $material)
    {
        $offer = $this->offerService->findByMaterial($material) ?: new Offer();
        $this->offerService->fillFromMaterial($offer, $material);
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool
    {
        return \is_object($data) && $data instanceof Materials;
    }
}
