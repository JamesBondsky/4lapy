<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SapBundle\Dto\In\Offers\Materials;
use FourPaws\SapBundle\Service\Materials\OfferService;
use FourPaws\SapBundle\Service\ReferenceService;
use Psr\Log\LoggerAwareInterface;

class OffersInfoConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ReferenceService
     */
    private $referenceService;
    /**
     * @var OfferService
     */
    private $offerService;

    public function __construct(ReferenceService $referenceService, OfferService $offerService)
    {
        $this->referenceService = $referenceService;
        $this->offerService = $offerService;
    }

    /**
     * @param Materials $offersInfo
     *
     * @return bool
     */
    public function consume($offersInfo): bool
    {
        if (!$this->support($offersInfo)) {
            return false;
        }
        $result = true;

        foreach ($offersInfo->getMaterials() as $material) {
            try {
                /**
                 * Создаем недостающие справочные даныне
                 */
                $this->referenceService->fillFromMaterial($material);

                $offer = new Offer();
                $this->offerService->fillFromMaterial($offer, $material);
                dump($offer);
//                $this->offerService->createFromMaterial($material);
            } catch (\Exception $exception) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $this->log()->error($exception->getMessage(), $exception->getTrace());
                $result = false;
                continue;
            }
        }

        return $result;
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
