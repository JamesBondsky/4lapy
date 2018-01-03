<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Фильтр списка акций по видам питомцев в разделе Акции
 * (шаблон кэшируется)
 *
 * @updated: 29.12.2017
 */

$this->setFrameMode(true);

/** cам html выводится в component_epilog */
