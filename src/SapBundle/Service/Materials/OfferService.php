<?php

namespace FourPaws\SapBundle\Service\Materials;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Service\ReferenceService;
use Psr\Log\LoggerAwareInterface;

class OfferService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ReferenceService
     */
    private $referenceService;

    /**
     * @var \CIBlockElement
     */
    private $iblockElement;

    /**
     * @var \Bitrix\Main\DB\Connection
     */
    private $connect;

    public function __construct(ReferenceService $referenceService)
    {
        $this->referenceService = $referenceService;
        $this->iblockElement = new \CIBlockElement();
        $this->connect = Application::getConnection();
    }


    /**
     * @param Material $material
     *
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function createFromMaterial(Material $material)
    {
        $this->connect->startTransaction();


        $this->connect->commitTransaction();
    }
}
