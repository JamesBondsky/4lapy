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
     * @Serializer\Type("int")
     * @var Prize[]
     */
    protected $prizes = [];

    /**
     * @Serializer\SerializedName("quest_status")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Quest\QuestStatus")
     * @var QuestStatus
     */
    protected $questStatus;
}
