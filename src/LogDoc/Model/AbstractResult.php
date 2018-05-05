<?php

namespace FourPaws\LogDoc\Model;

use FourPaws\LogDoc\Common\AbstractObject;

abstract class AbstractResult extends AbstractObject implements ResultInterface
{
    /** @var bool */
    private $isSuccess = false;
    /** @var mixed */
    private $result;

    /**
     * @return bool
     */
    function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @param bool $value
     * @return self
     */
    function setSuccess(bool $value): self
    {
        $this->isSuccess = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    function getResult()
    {
        return $this->result ?? null;
    }

    /**
     * @param mixed $value
     * @return self
     */
    function setResult($value): self
    {
        $this->result = $value;

        return $this;
    }
}
