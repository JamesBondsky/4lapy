<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use FourPaws\SapBundle\Dto\In\Offers\Materials;
use FourPaws\SapBundle\Exception\NotFoundPropertyException;
use FourPaws\SapBundle\Service\Materials\ProductService;
use FourPaws\SapBundle\Subscriber\BitrixEvents;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * Class MaterialsConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class MaterialsConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var MaterialConsumer
     */
    private $consumer;

    /**
     * @var ProductService
     */
    private $productService;

    /**
     * MaterialsConsumer constructor.
     *
     * @param MaterialConsumer $consumer
     * @param ProductService   $productService
     */
    public function __construct(MaterialConsumer $consumer, ProductService $productService)
    {
        $this->consumer = $consumer;
        $this->productService = $productService;
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

        $totalCount = $materials->getMaterials()->count();

        $this->log()->info(\sprintf('Импортируется %s документ', $materials->getDocumentNumber()));
        $this->log()->info(\sprintf('Импортируется %s материалов', $totalCount));

        $error = 0;
        foreach ($materials->getMaterials() as $material) {
            $materials->getMaterials()->removeElement($material);
            if ($this->consumer->consume($material)) {
                continue;
            }
            $error++;
        }

        $this->log()->log(LogLevel::INFO, 'Удаление товаров, не имеющих торговые предложения...');
        $this->productService->deleteEmptyProducts();

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
