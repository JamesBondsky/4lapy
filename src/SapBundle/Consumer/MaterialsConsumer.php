<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Db\SqlQueryException;
use FourPaws\SapBundle\Dto\In\Offers\Materials;
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
     * MaterialsConsumer constructor.
     *
     * @param MaterialConsumer $consumer
     */
    public function __construct(MaterialConsumer $consumer)
    {
        $this->consumer = $consumer;
    }
    
    /**
     * @param Materials $materials
     *
     * @throws SqlQueryException
     * @throws \RuntimeException
     *
     * @return bool
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
