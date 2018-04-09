<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Pipeline;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\SapBundle\Source\SourceInterface;

/**
 * Class Pipeline
 *
 * @package FourPaws\SapBundle\Pipeline
 */
class Pipeline implements PipelineInterface
{
    /**
     * @var Collection|SourceInterface[]
     */
    protected $sourceCollection;

    /**
     * Pipeline constructor.
     */
    public function __construct()
    {
        $this->sourceCollection = new ArrayCollection();
    }
    
    /**
     * @param SourceInterface $source
     *
     * @return bool
     */
    public function add(SourceInterface $source) : bool
    {
        return $this->sourceCollection->add($source);
    }
    
    /**
     * @inheritdoc
     */
    public function generator()
    {
        foreach ($this->sourceCollection as $source) {
            yield from $source->generator();
        }
    }
}
