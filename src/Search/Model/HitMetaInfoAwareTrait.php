<?php

namespace FourPaws\Search\Model;

trait HitMetaInfoAwareTrait
{
    /**
     * @var HitMetaInfo
     */
    protected $hitMetaInfo;

    /**
     * @return HitMetaInfo
     */
    public function getHitMetaInfo(): HitMetaInfo
    {
        if (is_null($this->hitMetaInfo)) {
            $this->hitMetaInfo = new HitMetaInfo();
        }

        return $this->hitMetaInfo;
    }

    /**
     * @param HitMetaInfo $hitMetaInfo
     *
     * @return $this
     */
    public function withHitMetaInfo(HitMetaInfo $hitMetaInfo)
    {
        $this->hitMetaInfo = $hitMetaInfo;

        return $this;
    }

}
