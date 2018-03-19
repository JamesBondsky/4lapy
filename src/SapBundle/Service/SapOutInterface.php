<?php

namespace FourPaws\SapBundle\Service;

/**
 * Interface SapOutInterface
 *
 * @package FourPaws\SapBundle\Service
 */
interface SapOutInterface
{
    /**
     * @param mixed $entity
     *
     * @return string
     */
    public function getFileName($entity): string;

    /**
     * @param string $outPath
     */
    public function setOutPath(string $outPath): void;

    /**
     * @param string $outPrefix
     */
    public function setOutPrefix(string $outPrefix): void;
}
