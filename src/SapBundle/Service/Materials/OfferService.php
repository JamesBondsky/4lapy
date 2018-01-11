<?php

namespace FourPaws\SapBundle\Service\Materials;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Repository\OfferRepository;
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
    /**
     * @var OfferRepository
     */
    private $offerRepository;

    public function __construct(OfferRepository $offerRepository, ReferenceService $referenceService)
    {
        $this->referenceService = $referenceService;
        $this->iblockElement = new \CIBlockElement();
        $this->connect = Application::getConnection();
        $this->offerRepository = $offerRepository;
    }


    /**
     * @param Material $material
     *
     * @return bool
     */
    public function createFromMaterial(Material $material)
    {
        $currentOffer = $this->offerRepository->findByXmlId($material->getOfferXmlId());
        /**
         * Дективируем не выгружаемые офферы
         */
        if ($currentOffer && $currentOffer->isActive() && $material->isNotUploadToIm()) {
            return $this->offerRepository->setActive($currentOffer->getId(), false);
        }


    }

    protected function performDeactivate(Material $material, Offer $offer = null)
    {
        if ($offer && $offer->isActive() && $material->isNotUploadToIm()) {
            return $this->offerRepository->setActive($offer->getId(), false);
        }
    }
}
