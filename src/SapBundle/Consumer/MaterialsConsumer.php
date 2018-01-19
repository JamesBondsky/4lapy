<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SapBundle\Dto\In\Offers\Materials;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

class MaterialsConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var MaterialConsumer
     */
    private $consumer;

    public function __construct(MaterialConsumer $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * @param Materials $materials
     *
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @return bool
     */
    public function consume($materials): bool
    {
        if (!$this->support($materials)) {
            return false;
        }

        $totalCount = $materials->getMaterials()->count();

        $this
            ->log()
            ->log(LogLevel::INFO, sprintf('Импортируется %s документ', $materials->getDocumentNumber()));
        $this
            ->log()
            ->log(LogLevel::INFO, sprintf('Импортируется %s материалов', $totalCount));

        $error = 0;
        foreach ($materials->getMaterials() as $material) {
            if ($this->consumer->consume($material)) {
                continue;
            }
            $error++;
        }
        $this
            ->log()
            ->log(LogLevel::INFO, sprintf('Импортировано %s товаров', $totalCount - $error));
        $this
            ->log()
            ->log(LogLevel::INFO, sprintf('Ошибка импорта %s товаров', $error));


        return $error > 0;
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
