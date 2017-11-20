<?php

namespace FourPaws\MobileApiBundle\Services;

use FourPaws\MobileApiBundle\Repository\UserSessionRepository;

class SecurityService
{
    /**
     * @var UserSessionRepository
     */
    private $repository;

    public function __construct(UserSessionRepository $repository)
    {
        $this->repository = $repository;
    }
}
