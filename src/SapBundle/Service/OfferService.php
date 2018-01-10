<?php

namespace FourPaws\SapBundle\Service;

use Bitrix\Main\Application;
use FourPaws\SapBundle\Dto\In\Offers\Material;

class OfferService
{
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


    public function createFromMaterial(Material $material)
    {

    }

    protected function createReference(Material $material)
    {
        return [

        ];
    }
}
