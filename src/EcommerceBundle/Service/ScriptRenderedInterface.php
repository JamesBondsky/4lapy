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
}
