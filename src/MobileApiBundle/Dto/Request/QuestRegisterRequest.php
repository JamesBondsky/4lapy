<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class QuestRegisterRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("code")
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     * @var $code
     */
    protected $code;

    /**
     * @Serializer\SerializedName("email")
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     * @var $email
     */
    protected $email;

    /**
     * @return mixed
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param $code
     * @return QuestRegisterRequest
     */
    public function setCode($code): QuestRegisterRequest
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param $email
     * @return QuestRegisterRequest
     */
    public function setEmail($email): QuestRegisterRequest
    {
        $this->email = $email;
        return $this;
    }
}