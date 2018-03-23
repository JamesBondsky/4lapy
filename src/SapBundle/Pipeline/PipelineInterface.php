<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Pipeline;

use FourPaws\SapBundle\Source\SourceMessageInterface;

interface PipelineInterface
{
    /**
     * @return \Generator|SourceMessageInterface[]
     */
    public function generator();
}
