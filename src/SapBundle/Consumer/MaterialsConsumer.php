<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CIBlock;
use Exception;
use FourPaws\CatalogBundle\EventController\Event;
use FourPaws\SapBundle\Dto\In\Offers\Materials;
use FourPaws\SapBundle\Exception\NotFoundPropertyException;
use FourPaws\SapBundle\Service\Materials\ProductService;
use FourPaws\SapBundle\Service\ReferenceService;
use FourPaws\SapBundle\Subscriber\BitrixEvents;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * Class MaterialsConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class MaterialsConsumer extends SapConsumerBase
{
    /**
     * @var MaterialConsumer
     */
    private $consumer;

    /**
     * @var ProductService
     */
    private $productService;
    /**
     * @var ReferenceService
     */
    private $referenceService;

    /**
     * MaterialsConsumer constructor.
     *
     * @param MaterialConsumer $consumer
     * @param ProductService $productService
     * @param ReferenceService $referenceService
     */
    public function __construct(
        MaterialConsumer $consumer,
        ProductService $productService,
        ReferenceService $referenceService
    ) {
        $this->consumer = $consumer;
        $this->productService = $productService;
        $this->referenceService = $referenceService;
    }

    /**
     * @param Materials $materials
     *
     * @return bool
     *
     * @throws NotFoundPropertyException
     * @throws RuntimeException
     * @throws SqlQueryException
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws ConfigurationException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function consume($materials): bool
    {
        if (!$this->support($materials)) {
            return false;
        }

        /**
         * Инициализация обработчиков очистки кеша значений справочников
         */
        try {
            BitrixEvents::initReferenceHandler();
        } catch (Exception $e) {
            /**
             * Не чистим значения справочника
             */
        }

        $materialsCollection = $materials->getMaterials();
        $totalCount = $materialsCollection->count();

        $this->log()->info(\sprintf('Импортируется %s документ', $materials->getDocumentNumber()));
        $this->log()->info(\sprintf('Импортируется %s материалов', $totalCount));

        // Лучше вообще переделать импорт так, чтобы referenceRepository апдейтился сразу для всех файлов Mat_, а не только по текущему
        Event::lockEvents();
        $this->referenceService->fillFromMaterials($materialsCollection);
        Event::unlockEvents();

        $error = 0;
        foreach ($materialsCollection as $material) {
            $materialsCollection->removeElement($material);
            if ($this->consumer->consume($material)) {
                continue;
            }
            $error++;
        }

        $this->log()->log(LogLevel::INFO, 'Удаление товаров, не имеющих торговые предложения...');
        CIBlock::disableClearTagCache();
        $this->productService->deleteEmptyProducts();
        CIBlock::enableClearTagCache();

        $this->log()->log(LogLevel::INFO, \sprintf('Импортировано %s товаров', $totalCount - $error));
        $this->log()->log(LogLevel::INFO, \sprintf('Ошибка импорта %s товаров', $error));

        return $error === 0;
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
