<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\Orm;

use Doctrine\Common\Collections\Collection;

class D7Repository implements D7RepositoryInterface
{

    /**
     * @var D7EntityManagerInterface
     */
    private $entityManager;

    public function __construct(D7EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id
     *
     * @throws \FourPaws\AppBundle\Exception\RuntimeException
     * @return null|object
     */
    public function find($id)
    {
        return $this->entityManager->find($id);
    }

    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return Collection|object[]
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): Collection {
        return $this->entityManager->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param object $object
     * @throws \FourPaws\BitrixOrmBundle\Exception\ValidationException
     * @throws \FourPaws\BitrixOrmBundle\Exception\DataManagerException
     * @return bool
     */
    public function create($object): bool
    {
        return $this->entityManager->create($object);
    }

    /**
     * @param object $object
     * @throws \FourPaws\BitrixOrmBundle\Exception\ValidationException
     * @throws \FourPaws\BitrixOrmBundle\Exception\DataManagerException
     * @return bool
     */
    public function update($object): bool
    {
        return $this->entityManager->update($object);
    }

    /**
     * @param $id
     * @throws \FourPaws\BitrixOrmBundle\Exception\DataManagerException
     * @return bool
     */
    public function delete($id): bool
    {
        return $this->entityManager->delete($id);
    }

    /**
     * @inheritdoc
     */
    public function count(array $criteria = []): int
    {
        return $this->entityManager->count($criteria);
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityManager->getEntityClass();
    }
}
