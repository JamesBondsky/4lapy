<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @noinspection AutoloadingIssuesInspection */

class ModifiedSliderComponent extends CBitrixComponent
{

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? getenv('GLOBAL_CACHE_TTL');
        return $params;
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $this->includeComponentTemplate();
        }
    }
}
