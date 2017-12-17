<?php

namespace FourPaws\Search\Model;

interface HitMetaInfoAwareInterface
{
    /**
     * Возвращает мета-информацию о результате поиска.
     *
     * @return HitMetaInfo
     */
    public function getHitMetaInfo(): HitMetaInfo;

    /**
     * Устанавливает мета-информацию о результате поиска.
     *
     * @param HitMetaInfo $hitMetaInfo
     *
     * @return mixed
     */
    public function withHitMetaInfo(HitMetaInfo $hitMetaInfo);
}
