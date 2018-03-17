<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Doctrine\Common\Collections\Collection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Repository\StockRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use WebArch\BitrixCache\BitrixCache;

class StockService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var StockRepository
     */
    protected $stockRepository;

    public function __construct(StockRepository $stockRepository)
    {
        $this->stockRepository = $stockRepository;
        $this->setLogger(LoggerFactory::create('StockService'));
    }


    /**
     * Получить наличие офферов на указанных складах
     *
     * @param Collection $offers
     * @param StoreCollection $stores
     */
    public function getStocks(Collection $offers, StoreCollection $stores): void
    {
        foreach ($offers as $offer) {
            $offer->withStocks(
                $this->getStocksByOffer($offer)
                    ->filterByStores($stores)
            );
        }
    }

    /**
     * @param Offer $offer
     *
     * @return StockCollection
     */
    public function getStocksByOffer(Offer $offer): StockCollection
    {
        $getStocks = function () use ($offer) {
            return $this->stockRepository->findBy(
                [
                    'PRODUCT_ID' => $offer->getId(),
                ]
            );
        };

        try {
            $data = (new BitrixCache())
                ->withId(__METHOD__ . '__' . $offer->getId())
                ->withTag('catalog:stocks')
                ->withTag('catalog:stocks:' . $offer->getId())
                ->withTag('catalog:offer:' . $offer->getId())
                ->resultOf($getStocks);

            return $data['result'];
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('failed to get stocks for offer: %s', $e->getMessage()),
                ['offer' => $offer->getId()]
            );
        }

        return new StockCollection();
    }
}
