<?php

namespace FourPaws\MobileApiBundle\Services\Security;

interface TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generate(): string;
}
