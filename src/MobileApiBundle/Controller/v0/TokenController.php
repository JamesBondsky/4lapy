<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\App\Application;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Dto\Response\StoreListResponse;
use FourPaws\MobileApiBundle\Services\Api\StoreService as ApiStoreService;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;
use FourPaws\MobileApiBundle\Services\Api\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class TokenController extends BaseController
{
    /**
     * @var ApiUserService
     */
    private $apiUserService;
    
    
    public function __construct(
        ApiUserService $apiUserService
    )
    {
        $this->apiUserService = $apiUserService;
    }
    
    /**
     * @Rest\Get(path="/disposable_token/", name="settings")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     * @throws \LogicException
     * @return ApiResponse
     *
     */
    public function getDisposableTokenAction(): ApiResponse
    {
        $disposableToken = $this->apiUserService->getDisposableToken();
        
        return (new ApiResponse())->setData([
            'disposableToken' => $disposableToken
        ]);
    }
}
