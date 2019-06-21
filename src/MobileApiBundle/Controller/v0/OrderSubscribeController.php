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
use FourPaws\MobileApiBundle\Dto\Response\OrderSubscribeListResponce;
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
    private $apiOrderSubcribeService;

    public function __construct(
        ApiOrderSubscribeService $apiOrderSubcribeService
    )
    {
        $this->apiOrderSubcribeService = $apiOrderSubcribeService;
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
        $orderSubscribeCollection = $this->apiOrderSubcribeService->getSubscriptionsByUser($USER->GetId());
        return new OrderSubscribeListResponce($orderSubscribeCollection);
    }



}