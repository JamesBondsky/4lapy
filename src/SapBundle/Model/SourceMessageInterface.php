<?php

namespace FourPaws\SapBundle\Model;

interface SourceMessageInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return mixed
     */
    public function getData();
}
