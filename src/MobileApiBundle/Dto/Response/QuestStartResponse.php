<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Quest\BarcodeTask;
use JMS\Serializer\Annotation as Serializer;

class QuestStartResponse
{
    /**
     * @Serializer\SerializedName("task_barcode")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Quest\BarcodeTask")
     * @var BarcodeTask
     */
    protected $barcodeTask;

    /**
     * @return BarcodeTask
     */
    public function getBarcodeTask(): BarcodeTask
    {
        return $this->barcodeTask;
    }

    /**
     * @param BarcodeTask $barcodeTask
     * @return QuestStartResponse
     */
    public function setBarcodeTask(BarcodeTask $barcodeTask): QuestStartResponse
    {
        $this->barcodeTask = $barcodeTask;
        return $this;
    }
}
