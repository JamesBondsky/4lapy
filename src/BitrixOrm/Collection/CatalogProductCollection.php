<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\CatalogProduct;
use JMS\Serializer\ArrayTransformerInterface;

class CatalogProductCollection extends CdbResultCollectionBase
{
    private $transformer;

    public function __construct(\CDBResult $result)
    {
        parent::__construct($result);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->transformer = Application::getInstance()->getContainer()->get(ArrayTransformerInterface::class);
    }

    /**
     * Извлечение модели
     */
    protected function fetchElement(): \Generator
    {
        while ($fields = $this->getCdbResult()->Fetch()) {
            yield $this->transformer->fromArray($fields, CatalogProduct::class, ['read']);
        }
    }
}
