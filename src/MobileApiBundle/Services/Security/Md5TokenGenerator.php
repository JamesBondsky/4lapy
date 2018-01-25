<?php

namespace FourPaws\MobileApiBundle\Services\Security;

use FourPaws\MobileApiBundle\Entity\Session;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;
use FourPaws\MobileApiBundle\Repository\UserSessionRepository;

class Md5TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @var UserSessionRepository
     */
    private $repository;

    public function __construct(UserSessionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws InvalidIdentifierException
     * @return string
     */
    public function generate(): string
    {
        do {
            $token = $this->strategy();
        } while ($this->checkExist($token));
        return $token;
    }

    /**
     * @return string
     */
    protected function strategy(): string
    {
        return md5(random_bytes(32));
    }

    /**
     * @param string $token
     * @throws InvalidIdentifierException
     * @return bool
     */
    protected function checkExist(string $token): bool
    {
        return $this->repository->findByToken($token) instanceof Session;
    }
}
