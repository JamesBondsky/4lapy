<?php

namespace FourPaws\SapBundle\Service\Materials;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\UpdateResult;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SapBundle\Dto\In\Offers\BarCode;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Enum\SapOfferProperty;
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
     * @var OfferRepository
     */
    private $offerRepository;

    public function __construct(ReferenceService $referenceService, OfferRepository $offerRepository)
    {
        $this->referenceService = $referenceService;
        $this->offerRepository = $offerRepository;
    }

    /**
     * @param Material $material
     *
     * @throws \FourPaws\SapBundle\Exception\RuntimeException
     * @return Offer
     */
    public function processMaterial(Material $material): Offer
    {
        $offer = $this->findByMaterial($material) ?: new Offer();
        $this->fillFromMaterial($offer, $material);

        return $offer;
    }

    /**
     * @param Offer $offer
     *
     * @return AddResult
     */
    public function create(Offer $offer): AddResult
    {
        return $this->offerRepository->create($offer);
    }

    /**
     * @param Offer $offer
     *
     * @return UpdateResult
     */
    public function update(Offer $offer): UpdateResult
    {
        return $this->offerRepository->update($offer);
    }

    /**
     * @param Material $material
     *
     * @return null|IblockElement|Offer
     */
    protected function findByMaterial(Material $material)
    {
        return $this->offerRepository->findByXmlId($material->getOfferXmlId());
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     * @return void
     */
    protected function fillFromMaterial(Offer $offer, Material $material)
    {
        $this->fillFields($offer, $material);
        $this->fillProperties($offer, $material);
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     */
    protected function fillFields(Offer $offer, Material $material)
    {
        $offer
            ->withActive(!$material->isNotUploadToIm())
            ->withName($material->getOfferName())
            ->withXmlId($material->getOfferXmlId());
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     */
    protected function fillProperties(Offer $offer, Material $material)
    {
        /**
         * @todo На данный момент нет описания полей по SAP
         * $offer->withFlavourCombination();
         * $offer->withColourCombination();
         */
        $offer->withMultiplicity($material->getCountInPack());
        $this->fillReferenceProperties($offer, $material);
        $this->fillBarCodes($offer, $material);
        $this->fillVolume($offer, $material);
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     */
    protected function fillVolume(Offer $offer, Material $material)
    {
        $offer->withVolume($material->getBasicUnitOfMeasure()->getVolume());
    }


    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     */
    protected function fillReferenceProperties(Offer $offer, Material $material)
    {
        $offer
            ->withColourXmlId($this->referenceService->getPropertyBitrixValue(SapOfferProperty::COLOUR, $material))
            ->withKindOfPackingXmlId($this->referenceService->getPropertyBitrixValue(
                SapOfferProperty::KIND_OF_PACKING,
                $material
            ))
            ->withClothingSizeXmlId($this->referenceService->getPropertyBitrixValue(
                SapOfferProperty::CLOTHING_SIZE,
                $material
            ))
            ->withVolumeReferenceXmlId($this->referenceService->getPropertyBitrixValue(
                SapOfferProperty::VOLUME,
                $material
            ))
            ->withSeasonYearXmlId($this->referenceService->getPropertyBitrixValue(
                SapOfferProperty::SEASON_YEAR,
                $material
            ));
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     */
    protected function fillBarCodes(Offer $offer, Material $material)
    {
        $barcodes = $material->getAllBarcodes()->map(function (BarCode $barCode) {
            return $barCode->getValue();
        })->toArray();
        $offer->withBarcodes($barcodes);
    }
}
