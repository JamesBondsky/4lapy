<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Pipeline;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\SapBundle\Exception\NotFoundPipelineException;
use FourPaws\SapBundle\Source\SourceMessage;
use Generator;

/**
 * Class PipelineRegistry
 *
 * @package FourPaws\SapBundle\Pipeline
 */
class PipelineRegistry
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * PipelineRegistry constructor.
     */
    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }

    /**
     * @param string $code
     * @param Pipeline $pipeline
     *
     * @return $this
     */
    public function register(string $code, Pipeline $pipeline)
    {
        $this->collection->set($code, $pipeline);

        return $this;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundPipelineException
     *
     * @return Pipeline
     */
    public function get(string $code): Pipeline
    {
        $result = $this->collection->get($code);

        if (!$result) {
            throw new NotFoundPipelineException(
                \sprintf(
                    'Cant find reference repository for %s property',
                    $code
                )
            );
        }

        return $result;
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
     * @return Collection|Pipeline[]
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * @param $code
     *
     * @throws NotFoundPipelineException
     *
     * @return Generator|SourceMessage[]
     */
    public function generator($code): Generator
    {
        yield from $this->get($code)->generator();
    }
}
