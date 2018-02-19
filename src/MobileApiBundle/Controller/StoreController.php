<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\MobileApiBundle\Dto\Response as Dto_responce;
use FourPaws\MobileApiBundle\Exception\MetroByNoneMetroCityException;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;

class StoreController extends FOSRestController
{
    /**@var StoreService */
    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * @Rest\Get(path="/shop_list/")
     * @Parameter(
     *     name="token",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="identifier token from /start request"
     * )
     * @Response(
     *     response="200"
     * )
     * @Rest\View()
     * @param StoreListRequest $storeListRequest
     *
     * @return Dto_responce
     * @throws \Exception
     */
    public function getListAction(StoreListRequest $storeListRequest): Dto_responce
    {
        $result = new Dto_responce();

        $filter = $this->storeService->getMobileFilterByRequest($storeListRequest);
        $storeCollection = $this->storeService->getStoreCollection([
            'filter' => $filter,
            'order'  => $this->storeService->getMobileOrderByRequest($storeListRequest),
        ]);

        /** @var Store $storeItem */
        if (!$storeCollection->isEmpty()) {
            list($servicesList, $metroList) = $this->storeService->getFullStoreInfo($storeCollection);
            $stores = new Dto_responce\StoreListResponse();
            foreach ($storeCollection as $storeItem) {
                $store = $stores->toApiFormat($storeItem, $servicesList, $metroList);
                $stores->addStore($store);
            }
            $result->setData($stores);
        } else {
            if(isset($filter['UF_METRO'])){
                unset($filter['UF_METRO']);
                $storeCollection = $this->storeService->getStoreCollection([
                    'filter' => $filter
                ]);
                if(!$storeCollection->isEmpty()){
                    $hasCityMetro = false;
                    foreach ($storeCollection as $storeItem) {
                        if($storeItem->getMetro() > 0){
                            $hasCityMetro = true;
                            break;
                        }
                    }
                    if(!$hasCityMetro){
                        throw new MetroByNoneMetroCityException('Данные о станциях метро для этого города отсутствуют', 44);
                    }

                    $result->setData(['shops'=>[]]);
                }
            }
            else{
                $result->setData(['shops'=>[]]);
            }
        }

        return $result;
    }
}
