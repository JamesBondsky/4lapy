<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Quest\BarcodeTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\Prize;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestStatus;
use JMS\Serializer\Annotation as Serializer;

class QuestQuestionTaskResponse
{
    /**
     * @Serializer\SerializedName("correct")
     * @Serializer\Type("bool")
     * @var boolean
     */
    protected $correct = false;

    /**
     * @Serializer\SerializedName("error_text")
     * @Serializer\Type("string")
     * @var string
     */
    protected $errorText = '';

    /**
     * @Serializer\SerializedName("task_barcode")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Quest\BarcodeTask")
     * @var BarcodeTask
     */
    protected $barcodeTask;

    /**
     * @Serializer\SerializedName("prizes")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Quest\Prize>")
     * @var Prize[]
     */
    protected $prizes = [];

    /**
     * @Serializer\SerializedName("quest_status")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Quest\QuestStatus")
     * @var QuestStatus
     */
    protected $questStatus;

    /**
     * @return bool
     */
    public function isCorrect(): bool
    {
        return $this->correct;
    }

    /**
     * @param bool $correct
     * @return QuestQuestionTaskResponse
     */
    public function setCorrect(bool $correct): QuestQuestionTaskResponse
    {
        $this->correct = $correct;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorText(): string
    {
        return $this->errorText;
    }

    /**
     * @param string $errorText
     * @return QuestQuestionTaskResponse
     */
    public function setErrorText(string $errorText): QuestQuestionTaskResponse
    {
        $this->errorText = $errorText;
        return $this;
    }

    /**
     * @return BarcodeTask
     */
    public function getBarcodeTask(): BarcodeTask
    {
        return $this->barcodeTask;
    }

    /**
     * @param BarcodeTask $barcodeTask
     * @return QuestQuestionTaskResponse
     */
    public function setBarcodeTask(BarcodeTask $barcodeTask): QuestQuestionTaskResponse
    {
        $this->barcodeTask = $barcodeTask;
        return $this;
    }

    /**
     * @return Prize[]
     */
    public function getPrizes(): array
    {
        return $this->prizes;
    }

    /**
     * @param Prize[] $prizes
     * @return QuestQuestionTaskResponse
     */
    public function setPrizes(array $prizes): QuestQuestionTaskResponse
    {
        $this->prizes = $prizes;
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
     */
    public function setQuestStatus(QuestStatus $questStatus): void
    {
        $this->questStatus = $questStatus;
    }
}
