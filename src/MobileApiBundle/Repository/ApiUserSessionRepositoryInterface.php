<?php

namespace FourPaws\MobileApiBundle\Repository;

use FourPaws\MobileApiBundle\Entity\ApiUserSession;
use FourPaws\MobileApiBundle\Exception\BitrixException;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use FourPaws\MobileApiBundle\Exception\WrongTransformerResultException;

interface ApiUserSessionRepositoryInterface
{
    /**
     * @param int $id
     *
     * @throws InvalidIdentifierException
     * @return null|ApiUserSession
     */
    public function find(int $id);

    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return ApiUserSession[]
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): array;

    /**
     * @param string $token
     *
     * @return null|ApiUserSession
     */
    public function findByToken(string $token);

    /**
     * @param ApiUserSession $session
     *
     * @throws ValidationException
     * @throws RuntimeException
     * @throws WrongTransformerResultException
     * @return bool
     */
    public function create(ApiUserSession $session): bool;

    /**
     * @param ApiUserSession $session
     * @throws ValidationException
     * @throws BitrixException
     * @throws WrongTransformerResultException
     * @return bool
     */
    public function update(ApiUserSession $session): bool;

    /**
     * @param int $id
     * @throws InvalidIdentifierException
     * @throws BitrixException
     * @return bool
     */
    public function delete(int $id): bool;
}
