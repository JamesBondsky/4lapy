<?php

namespace FourPaws\SapBundle\ReferenceDirectory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException;
use FourPaws\SapBundle\Repository\ReferenceRepository;

class ReferenceRepositoryRegistry
{
    /**
     * @var Collection
     */
    private $collection;

    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }

    /**
     * @param string              $code
     * @param ReferenceRepository $referenceRepository
     *
     * @return $this
     */
    public function register(string $code, ReferenceRepository $referenceRepository)
    {
        $this->collection->set($code, $referenceRepository);
        return $this;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundReferenceRepositoryException
     * @return ReferenceRepository
     */
    public function get(string $code): ReferenceRepository
    {
        if ($result = $this->collection->get($code)) {
            return $result;
        }
        throw new NotFoundReferenceRepositoryException(sprintf(
            'Cant find reference repository for %s property',
            $code
        ));
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function has(string $code): bool
    {
        return $this->collection->offsetExists($code);
    }

    /**
     * @return Collection|ReferenceRepository[]
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }
}
