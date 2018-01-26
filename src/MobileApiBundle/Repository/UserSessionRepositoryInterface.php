<?php

namespace FourPaws\MobileApiBundle\Repository;

use FourPaws\MobileApiBundle\Entity\Session;
use FourPaws\MobileApiBundle\Exception\BitrixException;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use FourPaws\MobileApiBundle\Exception\WrongTransformerResultException;

interface UserSessionRepositoryInterface
{
    /**
     * @param int $id
     * @throws InvalidIdentifierException
     * @return null|Session
     */
    public function find(int $id);

    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     * @return Session[]
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): array;

    /**
     * @param Session $session
     * @throws ValidationException
     * @throws RuntimeException
     * @throws WrongTransformerResultException
     * @return bool
     */
    public function create(Session $session): bool;

    /**
     * @param Session $session
     * @throws ValidationException
     * @throws BitrixException
     * @throws WrongTransformerResultException
     * @return bool
     */
    public function update(Session $session): bool;

    /**
     * @param int $id
     * @throws InvalidIdentifierException
     * @throws BitrixException
     * @return bool
     */
    public function delete(int $id): bool;
}
