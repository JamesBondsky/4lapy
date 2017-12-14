<?php

namespace FourPaws\Catalog\Model;

/**
 * Class Sorting
 *
 *
 *
 * @package FourPaws\Catalog\Model
 */
class Sorting
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $value = '';

    /**
     * @var bool
     */
    protected $selected = false;

    /**
     * @var array
     */
    protected $rule = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function withName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function withValue(string $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * @param bool $selected
     *
     * @return $this
     */
    public function withSelected(bool $selected)
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * @return array
     */
    public function getRule(): array
    {
        return $this->rule;
    }

    /**
     * @param array $rule
     *
     * @return $this
     */
    public function withRule(array $rule)
    {
        $this->rule = $rule;

        return $this;
    }



}
