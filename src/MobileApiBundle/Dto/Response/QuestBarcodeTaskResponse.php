<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use JMS\Serializer\Annotation as Serializer;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestionTask;
use FourPaws\MobileApiBundle\Dto\Object\Quest\QuestStatus;

class QuestBarcodeTaskResponse
{
    /**
     * @Serializer\SerializedName("result")
     * @Serializer\Type("int")
     * @var int
     */
    protected $result = 0;

    /**
     * @Serializer\SerializedName("correct_text")
     * @Serializer\Type("string")
     * @var string
     */
    protected $correctText = '';

    /**
     * @Serializer\SerializedName("error_text")
     * @Serializer\Type("string")
     * @var string
     */
    protected $errorText = '';

    /**
     * @Serializer\SerializedName("question_task")
     * @Serializer\Type("int")
     * @var QuestionTask
     */
    protected $questionTask;

    /**
     * @Serializer\SerializedName("quest_status")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Quest\QuestStatus")
     * @var QuestStatus
     */
    protected $questStatus;

    /**
     * @return int
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * @param int $result
     * @return QuestBarcodeTaskResponse
     */
    public function setResult(int $result): QuestBarcodeTaskResponse
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return string
     */
    public function getCorrectText(): string
    {
        return $this->correctText;
    }

    /**
     * @param string $correctText
     * @return QuestBarcodeTaskResponse
     */
    public function setCorrectText(string $correctText): QuestBarcodeTaskResponse
    {
        $this->correctText = $correctText;
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
     * @return QuestBarcodeTaskResponse
     */
    public function setErrorText(string $errorText): QuestBarcodeTaskResponse
    {
        $this->errorText = $errorText;
        return $this;
    }

    /**
     * @return QuestionTask
     */
    public function getQuestionTask(): QuestionTask
    {
        return $this->questionTask;
    }

    /**
     * @param QuestionTask $questionTask
     * @return QuestBarcodeTaskResponse
     */
    public function setQuestionTask(QuestionTask $questionTask): QuestBarcodeTaskResponse
    {
        $this->questionTask = $questionTask;
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
     * @return QuestBarcodeTaskResponse
     */
    public function setQuestStatus(QuestStatus $questStatus): QuestBarcodeTaskResponse
    {
        $this->questStatus = $questStatus;
        return $this;
    }
}
