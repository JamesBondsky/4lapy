<?php

namespace FourPaws\MobileApiBundle\Dto;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

class Response
{
    /**
     * @Serializer\SerializedName("data")
     * @var mixed
     */
    protected $data;

    /**
     * @Serializer\Type("ArrayCollection<FourPaws\MobileApiBundle\Dto\Error>")
     * @Serializer\SerializedName("error")
     * @var Collection|Error[]
     */
    protected $errors;

    public function __construct()
    {
        $this->errors = new ArrayCollection();
    }

    /**
     * @return Collection|Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param Collection|Error[] $errors
     * @return Response
     */
    public function setErrors(Collection $errors): Response
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @param Error $error
     * @return bool
     */
    public function addError(Error $error): bool
    {
        return $this->errors->add($error);
    }

    /**
     * @param Error $error
     * @return bool
     */
    public function removeError(Error $error): bool
    {
        return $this->errors->removeElement($error);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return Response
     */
    public function setData($data): Response
    {
        $this->data = $data;
        return $this;
    }
}
