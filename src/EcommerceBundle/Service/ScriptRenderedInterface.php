<?php

namespace FourPaws\EcommerceBundle\Service;

/**
 * Interface ScriptRenderedInterface
 *
 * @package FourPaws\EcommerceBundle\Service
 */
interface ScriptRenderedInterface
{
    /**
     * @param $data
     * @param bool $addScriptTag
     *
     * @return string
     */
    public function renderScript($data, bool $addScriptTag): string;

    /**
     * @param $data
     * @param string $name
     * @param bool $addScriptTag
     *
     * @return string
     */
    public function renderPreset($data, string $name, bool $addScriptTag): string;
}
