<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use CIBlock;
use FourPaws\CatalogBundle\EventController\Event;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SapBundle\Consumer\ConsumerRegistryInterface;
use FourPaws\SapBundle\Dto\In\DcStock\DcStock;
use FourPaws\SapBundle\Dto\In\Offers\Materials;
use FourPaws\SapBundle\Exception\NotFoundPipelineException;
use FourPaws\SapBundle\Pipeline\PipelineRegistry;
use FourPaws\SapBundle\Service\Materials\ProductService;
use FourPaws\SapBundle\Source\SourceRegistryInterface;

/**
 * Class SapService
 *
 * @package FourPaws\SapBundle\Service
 */
class SapService
{
    /**
     * @var ConsumerRegistryInterface
     */
    private $consumerRegistry;

    /**
     * @var SourceRegistryInterface
     */
    private $sourceRegistry;

    /**
     * @var PipelineRegistry
     */
    private $pipelineRegistry;

    /**
     * @var ProductService
     */
    private $productService;

    /**
     * SapService constructor.
     *
     * @param ConsumerRegistryInterface $consumerRegistry
     * @param SourceRegistryInterface $sourceRegistry
     * @param PipelineRegistry $pipelineRegistry
     * @param ProductService $productService
     */
    public function __construct(
        ConsumerRegistryInterface $consumerRegistry,
        SourceRegistryInterface $sourceRegistry,
        PipelineRegistry $pipelineRegistry,
        ProductService $productService
    )
    {
        $this->consumerRegistry = $consumerRegistry;
        $this->sourceRegistry = $sourceRegistry;
        $this->pipelineRegistry = $pipelineRegistry;
        $this->productService = $productService;
    }

    /**
     * @param string $pipelineCode
     * @throws NotFoundPipelineException
     * @throws IblockNotFoundException
     */
    public function execute(string $pipelineCode): void
    {
        Manager::disableExtendsDiscount();
        $needProductCacheClear = false;
        $needIblockTagCacheClear = false;

        foreach ($this->pipelineRegistry->generator($pipelineCode) as $sourceMessage) {
            if ($this->consumerRegistry->consume($sourceMessage->getData())) {
                $this->sourceRegistry->ack($sourceMessage);
                if ($sourceMessage->getType() === DcStock::class || $sourceMessage->getType() === Materials::class) {
                    $needProductCacheClear = true;
                }
                if ($sourceMessage->getType() === Materials::class) {
                    $needIblockTagCacheClear = true;
                }

                continue;
            }

            $this->sourceRegistry->noAck($sourceMessage);
        }

        if ($needIblockTagCacheClear) {
            CIBlock::clearIblockTagCache(IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS));
            CIBlock::clearIblockTagCache(IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS));
            sleep(60); // Добавление интервала между сбросами кэша снижает нагрузку и время нагрузки, создаваемой импортом
        }
        if ($needProductCacheClear && ($productsToClearCache = $this->productService->getProductsToClearCache())) {
            foreach ($productsToClearCache as $productId) {
                Event::clearProductCache($productId);
            }
        }
    }
}
