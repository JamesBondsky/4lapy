<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use Bitrix\Main\Web\Uri;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\Image;
use FourPaws\MobileApiBundle\Dto\Object\Store\Store;
use FourPaws\MobileApiBundle\Dto\Object\Store\StoreService;
use FourPaws\StoreBundle\Entity\Store as Entity_Store;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class StoreListResponse
 * @package FourPaws\MobileApiBundle\Dto\Response
 */
class StoreListResponse
{
    /**
     * @var Store[]
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Store\Store>")
     * @Serializer\SerializedName("shops")
     */
    protected $shops = [];

    /**
     * @return Store[]
     */
    public function getShops(): array
    {
        return $this->shops ?? [];
    }

    /**
     * @param Store[] $shops
     *
     * @return StoreListResponse
     */
    public function setShops(array $shops): StoreListResponse
    {
        $this->shops = $shops;
        return $this;
    }

    public function addStore(Store $store)
    {
        $this->shops[] = $store;
    }

    /**
     * @param Entity_Store $store
     * @param array        $servicesList
     * @param array        $metroList
     *
     * @return Store
     */
    public function toApiFormat(
        Entity_Store $store,
        array $servicesList,
        array $metroList
    ): Store {
        $result = new Store();

        $metroId = $store->getMetro();
        $metroName = $metroId > 0 ? $metroList[$metroId]['UF_NAME'] : '';
        $metroAddressText = $metroId > 0 ? 'м.' . $metroName . ', ' : '';
        $metroColor = $metroId > 0 ? '#' . $metroList[$metroId]['BRANCH']['UF_COLOUR_CODE'] : '';

        $services = [];
        foreach ($servicesList as $serviceItem) {
            $service = new StoreService();
            $service->setTitle($serviceItem['UF_NAME']);

            $image = '';
            if ($serviceItem['UF_FILE'] > 0) {
                try {
                    $image = Image::createFromPrimary($serviceItem['UF_FILE'])->getSrc();
                } catch (FileNotFoundException $e) {
                }
            }
            $service->setImage($image);

            $services[] = $service;
        }

        $result->setAddress($metroAddressText . $store->getAddress());
        /** @todo для запроса "shops_list_available"
         * рассчет
         * if ($this->pickupDelivery) {
         * $stockResult = $this->getStockResult($this->pickupDelivery);
         * $storeAmount = reset($this->offers)->getStocks()
         * ->filterByStores(
         * $this->storeService->getByCurrentLocation(
         * StoreService::TYPE_STORE
         * )
         * )->getTotalAmount();
         * }
         */
        $result->setAvailabilityStatus('');
        $result->setCityId($store->getLocation());
        $result->setDetails($store->getDescription());
        $result->setLatitude($store->getLatitude());
        $result->setLongitude($store->getLongitude());
        $result->setMetroColor($metroColor);
        $result->setMetroName($metroName);
        $result->setPhone($store->getPhone());
        /** @todo добавочного номера нет */
        $result->setPhoneExt('');
        $result->setPicture($store->getSrcImage());
        $result->setService($services);
        $result->setTitle($store->getTitle());
        /** @todo нет детального магазина - поставлен url на список */
        $uri = new Uri('http://' . SITE_SERVER_NAME . '/company/shops/');
        $uri->addParams(['city' => $store->getLocation(), 'id' => $store->getId()]);
        $result->setUrl($uri->getUri());
        $result->setWorkTime($store->getSchedule());
        return $result;
    }
}
