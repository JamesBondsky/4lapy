<?php

namespace FourPaws\CatalogBundle\Translate;

/**
 * Interface TranslateInterface
 *
 * @package FourPaws\CatalogBundle\Translate
 */
interface TranslateInterface
{
    /** @noinspection ReturnTypeCanBeDeclaredInspection
     *
     * @param $data
     *
     * @return @mixed
     */
    public function translate($data);
}
