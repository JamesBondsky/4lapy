<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 20.06.2019
 * Time: 17:33
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\OrderSubscribeRequest;
use FourPaws\MobileApiBundle\Dto\Response\OrderSubscribeListResponce;
use FourPaws\MobileApiBundle\Dto\Response\OrderSubscribeResponce;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FourPaws\PersonalBundle\Service\OrderSubscribeService as ApiOrderSubscribeService;

/**
 * Class OrderSubscribeController
 * @package FourPaws\MobileApiBundle\Controller
 * @Security("has_role('REGISTERED_USERS')")
 */
class OrderSubscribeController extends FOSRestController
{
    /**
     * @var ApiOrderSubscribeService
     */
    private $apiOrderSubscribeService;

    public function __construct(
        ApiOrderSubscribeService $apiOrderSubscribeService
    )
    {
        $this->apiOrderSubscribeService = $apiOrderSubscribeService;
    }

    /**
     * @Rest\Get(path="/order_subscribe_list/")
     * @Rest\View()
     * @return OrderSubscribeListResponce
     * @throws \Exception
     */
    public function getOrderSubscribeListAction()
    {
        global $USER;
        $orderSubscribeCollection = $this->apiOrderSubscribeService->getSubscriptionsByUser($USER->GetId());
        return new OrderSubscribeListResponce($orderSubscribeCollection);
    }

    /**
     * @Rest\Get(path="/order_subscribe/")
     * @Rest\View()
     * @return OrderSubscribeResponce
     * @throws \Exception
     */
    public function getOrderSubscribeAction(OrderSubscribeRequest $request)
    {
        $orderSubscribe = $this->apiOrderSubscribeService->getById($request->getOrderSubscribeId());
        $responce = new OrderSubscribeResponce($orderSubscribe);
        return $responce;
    }
}