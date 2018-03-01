<?php

namespace FourPaws\MobileApiBundle\TestController;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;
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
     * @Rest\Delete("/session/{tokenToDelete}/")
     * @Rest\View()
     * @param string $tokenToDelete
     *
     * @return array
     * @throws \FourPaws\MobileApiBundle\Exception\BitrixException
     */
    public function deleteAction(string $tokenToDelete): array
    {
        $status = true;
        try {
            $status = $this->apiUserSessionRepository->deleteByToken($tokenToDelete);
        } catch (InvalidIdentifierException $exception) {
        }
        return ['status' => $status];
    }
}