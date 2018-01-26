<?php

namespace FourPaws\SapBundle\Pipeline;

use FourPaws\SapBundle\Source\SourceMessageInterface;

interface PipelineInterface
{
    /**
     * @return \Generator|SourceMessageInterface[]
     */
    public function generator();
}
