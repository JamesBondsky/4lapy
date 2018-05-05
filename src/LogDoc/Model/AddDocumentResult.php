<?php

namespace FourPaws\LogDoc\Model;

class AddDocumentResult extends AbstractResult implements ResultInterface
{
    /** @var string|int */
    private $id = '';

    /**
     * @return string|int
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $value
     * @return self
     */
    function setId($value): self
    {
        $this->id = $value;

        return $this;
    }
}
