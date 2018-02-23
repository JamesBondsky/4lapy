<?php

namespace FourPaws\MobileApiBundle\TestController;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;

/**
 * Class TestSessionController
 * @package FourPaws\MobileApiBundle\ControllerTest
 */
class TestSessionController extends FOSRestController
{
    /**
     * @var ApiUserSessionRepository
     */
    private $apiUserSessionRepository;

    public function __construct(ApiUserSessionRepository $apiUserSessionRepository)
    {
        $this->apiUserSessionRepository = $apiUserSessionRepository;
    }

    /**
     * @Rest\Delete("/session/{token}/")
     * @Rest\View()
     * @param string $token
     *
     * @return array
     * @throws \FourPaws\MobileApiBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\MobileApiBundle\Exception\BitrixException
     */
    public function deleteAction(string $token): array
    {
        return ['status' => $this->apiUserSessionRepository->deleteByToken($token)];
    }
}