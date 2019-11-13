<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Quest;

use JMS\Serializer\Annotation as Serializer;

class QuestionTask
{
    public const STATUS_NOT_START = 0;
    public const STATUS_SUCCESS_COMPLETE = 1;
    public const STATUS_FAIL_COMPLETE = 2;

    /**
     * @Serializer\SerializedName("title")
     * @Serializer\Type("string")
     * @var string
     */
    protected $question = '';

    /**
     * @Serializer\SerializedName("variants")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Quest\AnswerVariant>")
     * @var AnswerVariant[]
     */
    protected $variants = [];

    /**
     * @return string
     */
    public function getQuestion(): string
    {
        return $this->question;
    }

    /**
     * @param string $question
     * @return QuestionTask
     */
    public function setQuestion(string $question): QuestionTask
    {
        $this->question = $question;
        return $this;
    }

    /**
     * @return AnswerVariant[]
     */
    public function getVariants(): array
    {
        return $this->variants;
    }

    /**
     * @param AnswerVariant[] $variants
     * @return QuestionTask
     */
    public function setVariants(array $variants): QuestionTask
    {
        $this->variants = $variants;
        return $this;
    }
}
