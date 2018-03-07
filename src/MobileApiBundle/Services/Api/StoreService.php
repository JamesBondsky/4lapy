<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\Web\Uri;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\Image;
use FourPaws\MobileApiBundle\Dto\Object\Store\Store as ApiStore;
use FourPaws\MobileApiBundle\Dto\Object\Store\StoreService as ApiStoreServiceDto;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService as AppStoreService;

class StoreService
{
    /**
     * @var AppStoreService
     */
    private $appStoreService;

    public function __construct(AppStoreService $appStoreService)
    {
        $this->appStoreService = $appStoreService;
    }

    /**
     * @param StoreListRequest $storeListRequest
     *
     * @throws \Exception
     * @throws \Bitrix\Main\ArgumentException
     * @return ApiStore[]|\Doctrine\Common\Collections\Collection
     */
    public function getList(StoreListRequest $storeListRequest): Collection
    {
        $appStoreCollection = $this->appStoreService->getStoreCollection(
            $this->getParams($storeListRequest)
        );
        if (0 === $appStoreCollection->count()) {
            $cloneRequest = clone $storeListRequest;
            $cloneRequest->setMetroStation([]);
            /**
             * @todo nearest city
             */
            $appStoreCollection = $this->appStoreService->getStoreCollection(
                $this->getParams($cloneRequest)
            );
        }
        $storeInfo = $this->appStoreService->getFullStoreInfo($appStoreCollection);
        return $appStoreCollection->map(function (Store $store) use ($storeInfo) {
            return $this->toApiFormat($store, ...$storeInfo);
        });
    }

    protected function getParams(StoreListRequest $storeListRequest)
    {
        return [
            'filter' => $this->getFilter($storeListRequest),
            'order'  => $this->getOrder($storeListRequest),
        ];
    }

    protected function getFilter(StoreListRequest $storeListRequest)
    {
        $result = [];
        if (!empty($storeListRequest->getMetroStation())) {
            $result['UF_METRO'] = $storeListRequest->getMetroStation();
        }
        if (!empty($storeListRequest->getCityId())) {
            $result['UF_LOCATION'] = $storeListRequest->getCityId();
        }

        return $result;
    }

    protected function getOrder(StoreListRequest $storeListRequest)
    {
        $result = [];
        //Сортировка по приближенности к текущему местоположению
        $longitude = $storeListRequest->getLongitude();
        $latitude = $storeListRequest->getLatitude();
        if ($longitude > 0 && $latitude > 0) {
            $result['DISTANCE_' . (string)$latitude . '_' . (string)$longitude] = 'ASC';
        }

        return $result;
    }

    protected function toApiFormat(Store $store, array $servicesList, array $metroList): ApiStore
    {
        $result = new ApiStore();

        $metroId = $store->getMetro();
        $metroName = $metroId > 0 ? $metroList[$metroId]['UF_NAME'] : '';
        $metroAddressText = $metroId > 0 ? 'м.' . $metroName . ', ' : '';
        $metroColor = $metroId > 0 ? '#' . $metroList[$metroId]['BRANCH']['UF_COLOUR_CODE'] : '';

        $services = [];
        foreach ($servicesList as $serviceItem) {
            $service = new ApiStoreServiceDto();
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
