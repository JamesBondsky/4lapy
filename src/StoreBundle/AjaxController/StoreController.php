<?php

namespace FourPaws\StoreBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Location\LocationService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class StoreController
 *
 * @package FourPaws\UserBundle\Controller
 * @Route("/")
 */
class StoreController extends Controller
{
    /**
     * @Route("/shop-list/", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function shopListAction(Request $request): JsonResponse
    {
        /** @var LocationService $locationService */
        $locationService = $this->get('location.service');
        /** @var StoreService $storeService */
        $storeService = $this->get('store.service');
        $location = $request->query->get('code') ?? $locationService->getCurrentLocation();
        
        $shopList = $storeService->getByLocation($location, StoreService::TYPE_SHOP);
        
        $result = [];
        /** @var Store $shop */
        foreach ($shopList as $shop) {
            $result[] = [
                'NAME' => $shop->getTitle(),
                'CODE' => $shop->getXmlId(),
                'ADDRESS' => $shop->getAddress(),
                'GPS' => [
                    'LATITUDE' => $shop->getLatitude(),
                    'LONGITUDE' => $shop->getLongitude()
                ]
            ];
        }
        
        return JsonSuccessResponse::createWithData('', $result);
    }
}
