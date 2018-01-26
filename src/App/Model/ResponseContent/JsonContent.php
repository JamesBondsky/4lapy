<?php

namespace FourPaws\App\Model\ResponseContent;

use JsonSerializable;

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

    /**
     * @var bool
     */
    private $reload = false;

    /**
     * @var string
     */
    private $redirect = '';

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
    public function withData($data) : JsonContent
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

    public function getReload(): bool
    {
        return (bool)$this->reload;
    }

    public function withReload(bool $reload): JsonContent
    {
        $this->reload = $reload;
        return $this;
    }

    public function getRedirect(): string
    {
        return $this->redirect;
    }

    public function withRedirect(string $redirect): JsonContent
    {
        $this->redirect = $redirect;

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
