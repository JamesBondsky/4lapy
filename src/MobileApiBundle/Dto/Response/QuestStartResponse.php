<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Quest\BarcodeTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestStatus;
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
     * @Serializer\SerializedName("quest_status")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Quest\QuestStatus")
     * @var QuestStatus
     */
    protected $questStatus;

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

    /**
     * @return QuestStatus
     */
    public function getQuestStatus(): QuestStatus
    {
        return $this->questStatus;
    }

    /**
     * @param QuestStatus $questStatus
     * @return QuestStartResponse
     */
    public function setQuestStatus(QuestStatus $questStatus): QuestStartResponse
    {
        $this->questStatus = $questStatus;
        return $this;
    }
}
