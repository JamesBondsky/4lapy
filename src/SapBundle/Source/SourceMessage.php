<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Source;

/**
 * Class SourceMessage
 *
 * @package FourPaws\SapBundle\Source
 */
class SourceMessage implements SourceMessageInterface
{
    private $id;

    private $type;

    private $data;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Create source message
     *
     * @param        $id
     * @param string $type
     * @param string $data
     */
    public function __construct(string $id, string $type, $data)
    {
        $this->id = $id;
        $this->type = $type;
        $this->data = $data;
    }
}
