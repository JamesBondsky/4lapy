<?php

namespace FourPaws\MobileApiBundle\Repository;

use FourPaws\MobileApiBundle\Entity\ApiPushEvent;
use FourPaws\MobileApiBundle\Exception\BitrixException;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use FourPaws\MobileApiBundle\Exception\WrongTransformerResultException;

interface ApiPushEventRepositoryInterface
{
    /**
     * @param int $id
     *
     * @throws InvalidIdentifierException
     * @return null|ApiPushEvent
     */
    public function find(int $id);

    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return ApiPushEvent[]
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): array;

    /**
     * @param ApiPushEvent $pushEvent
     *
     * @throws ValidationException
     * @throws RuntimeException
     * @throws WrongTransformerResultException
     * @return bool
     */
    public function create(ApiPushEvent $pushEvent): bool;

    /**
     * @param ApiPushEvent $pushEvent
     * @throws ValidationException
     * @throws BitrixException
     * @throws WrongTransformerResultException
     * @return bool
     */
    public function update(ApiPushEvent $pushEvent): bool;

    /**
     * @param int $id
     * @throws InvalidIdentifierException
     * @throws BitrixException
     * @return bool
     */
    public function delete(int $id): bool;
}
