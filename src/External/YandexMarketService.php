<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\External;


use Adv\YandexMarketApi\Model\Campaign;
use Adv\YandexMarketApi\Model\OfferPrice\Feed;
use Adv\YandexMarketApi\Model\OfferPrice\OfferPrice;
use Adv\YandexMarketApi\Model\OfferPrice\OfferPrices;
use Adv\YandexMarketApi\Model\OfferPrice\Price;
use Adv\YandexMarketApi\Response\BaseMethods\CampaignsResponse;
use Adv\YandexMarketApi\YandexMarketApiClient;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\External\Exception\YandexMarketApiException;
use FourPaws\External\Exception\YandexMarketCampaignNotFoundException;
use FourPaws\External\Exception\YandexMarketOfferNotFoundException;

class YandexMarketService
{
    public const CAMPAIGN_STATE_ACTIVE     = 1;
    public const CAMPAIGN_STATE_DISABLED   = 2;
    public const CAMPAIGN_STATE_ACTIVATING = 3;
    public const CAMPAIGN_STATE_DISABLING  = 4;

    public const MAX_OFFERS_TO_UPDATE = 2000;

    /**
     * @var YandexMarketApiClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $campaignId;

    /**
     * @var int
     */
    protected $feedId;

    /**
     * YandexMarketService constructor.
     * @param string $clientId
     * @param string $token
     * @param int    $feedId
     * @param string $campaignId
     */
    public function __construct(string $clientId, string $token, int $feedId = 0, string $campaignId = '')
    {
        $this->client = new YandexMarketApiClient($clientId, $token);
        $this->campaignId = $campaignId;
        $this->feedId = $feedId;
    }

    /**
     * @param int $offerId
     *
     * @return bool
     */
    public function updateOfferById(int $offerId)
    {
        if (!$offer = OfferQuery::getById($offerId)) {
            throw new YandexMarketOfferNotFoundException(\sprintf('Offer #%s not found', $offerId));
        }

        return $this->updateOffer($offer);
    }

    /**
     * @param Offer $offer
     *
     * @return bool
     */
    public function updateOffer(Offer $offer): bool
    {
        return $this->updateOffers(new ArrayCollection([$offer]));

    }

    /**
     * @param Collection $offers
     *
     * @return bool
     */
    public function updateOffers(Collection $offers): bool
    {
        $prices = $this->generateOfferPrices($offers);

        $response = $this->client->pricesMethods()->updates($this->campaignId, $prices);
        if (!$response->isOk()) {
            throw new YandexMarketApiException(
                \sprintf('Failed to update offer prices: %s', $response->getErrorMessage())
            );
        }

        return true;
    }

    /**
     * @return bool
     * @throws YandexMarketApiException
     */
    public function deleteAllPrices(): bool
    {
        $response = $this->client->pricesMethods()->removals($this->campaignId);
        if (!$response->isOk()) {
            throw new YandexMarketApiException(\sprintf('Failed to delete prices: %s', $response->getErrorMessage()));
        }

        return true;
    }

    /**
     * @param string $domain
     *
     * @return Campaign
     * @throws YandexMarketCampaignNotFoundException
     * @throws YandexMarketApiException
     */
    public function getCampaign(string $domain): Campaign
    {
        $result = null;
        $response = $this->client->baseMethods()->campaigns();
        if (!$response->isOk()) {
            throw new YandexMarketApiException($response->getErrorMessage());
        }

        /**
         * @var CampaignsResponse $response
         * @var Campaign          $campaign
         */
        foreach ($response->getCampaigns() as $campaign) {
            if ($campaign->getState() !== static::CAMPAIGN_STATE_ACTIVE) {
                continue;
            }

            if ($campaign->getDomain() !== $domain) {
                continue;
            }

            $result = $campaign;
        }

        if (null === $result) {
            throw new YandexMarketCampaignNotFoundException(\sprintf('Active campaign for domain %s not found', $domain));
        }

        return $result;
    }

    /**
     * @param Collection $offers
     *
     * @return OfferPrices
     */
    protected function generateOfferPrices(Collection $offers): OfferPrices
    {
        $result = new OfferPrices();

        $feed = (new Feed())->withId($this->feedId);
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            $price = (new Price())
                ->withDiscountBase($offer->getCatalogOldPrice())
                ->withValue($offer->getPrice());

            $result->addOfferPrice(
                (new OfferPrice())
                    ->withId($offer->getId())
                    ->withFeed($feed)
                    ->withPrice($price)
            );
        }

        return $result;
    }
}