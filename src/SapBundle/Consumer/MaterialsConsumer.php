<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Db\SqlQueryException;
use FourPaws\SapBundle\Dto\In\Offers\Materials;
use FourPaws\SapBundle\Service\Materials\ProductService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

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
     * @throws SqlQueryException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\Config\ConfigurationException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function consume($materials) : bool
    {
        if (!$this->support($materials)) {
            return false;
        }
    
        $totalCount = $materials->getMaterials()->count();
    
        $this->log()->log(LogLevel::INFO, \sprintf('Импортируется %s документ', $materials->getDocumentNumber()));
        $this->log()->log(LogLevel::INFO, \sprintf('Импортируется %s материалов', $totalCount));
        
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
    public function support($data) : bool
    {
        return \is_object($data) && $data instanceof Materials;
    }
}
