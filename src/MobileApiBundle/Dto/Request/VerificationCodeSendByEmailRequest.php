<?php


namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class VerificationCodeSendByEmailRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("code")
     * @Serializer\Type("string")
     * @var string
     * @Assert\NotBlank()
     */
    protected $code;

    /**
     * @Serializer\SerializedName("email")
     * @Serializer\Type("string")
     * @var string
     * @Assert\NotBlank()
     */
    protected $email;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
