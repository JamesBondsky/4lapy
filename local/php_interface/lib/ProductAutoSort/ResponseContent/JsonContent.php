<?php

namespace FourPaws\ProductAutoSort\ResponseContent;

use JsonSerializable;

/**
 * Class JsonContent
 * @package FourPaws\ProductAutoSort\ResponseContent
 *
 * TODO Объект может понадобиться и в других модулях. Как-то вынести его позже.
 */
class JsonContent implements JsonSerializable
{
    /**
     * @var int
     */
    private $success;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $message;

    public function __construct(string $message = '', bool $success = true, $data = null)
    {
        $this->message = $message;
        $this->success = (int)$success;
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function getSuccess(): bool
    {
        return (bool)$this->success;
    }

    /**
     * @param bool $success
     *
     * @return JsonContent
     */
    public function withSuccess(bool $success): JsonContent
    {
        $this->success = (int)$success;

        return $this;
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
     *
     * @return JsonContent
     */
    public function withData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return JsonContent
     */
    public function withMessage(string $message): JsonContent
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize() : array
    {
        return get_object_vars($this);
    }
}
