<?php

namespace FourPaws\SapBundle\Pipeline;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\SapBundle\Source\SourceInterface;

class Pipeline implements PipelineInterface
{
    /**
     * @var SourceInterface[]|Collection
     */
    protected $sourceCollection;
    
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
