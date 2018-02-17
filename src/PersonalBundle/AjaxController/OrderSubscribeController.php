<?php

namespace FourPaws\PersonalBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Service\PetService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrderSubscribeController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/orderSubscribe")
 */
class OrderSubscribeController extends Controller
{
    /**
     * @var OrderSubscribeService
     */
    private $orderSubscribeService;
    
    public function __construct(OrderSubscribeService $orderSubscribeService) {
        $this->orderSubscribeService = $orderSubscribeService;
    }
    
    /**
     * @Route("/add/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addAction(Request $request) : JsonResponse
    {
    }
    
    /**
     * @Route("/update/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request) : JsonResponse
    {
    }
    
    /**
     * @Route("/delete/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request) : JsonResponse
    {
    }
}
