<?php

namespace LinguaLeo\ExpertSender\Entities;

class Snippet
{
    protected $name;
    protected $value;

    public function __construct($name, $value, $isHtml = false)
    {
        $this->name = $name;
        $this->value = $value;
        if ($isHtml) {
            $this->value = sprintf('<![CDATA[%s]]>', $value);
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
