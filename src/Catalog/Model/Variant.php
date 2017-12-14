<?php

namespace FourPaws\Catalog\Model;

class Variant
{
    /**
     * @var string Название варианта фильтра
     */
    private $name = '';

    /**
     * @var string Значение варианта фильтра. Оно же является уникальным кодом варианта.
     */
    private $value = '';

    /**
     * @var int Количество продуктов, которые соответствуют этому варианту.
     */
    private $count = 0;

    /**
     * @var bool Вариант выбран.
     */
    private $checked = false;

    /**
     * @var bool Вариант доступен - его выбор даст непустой результат фильтрации.
     */
    private $available = true;

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
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return $this
     */
    public function withCount(int $count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return bool
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }

    /**
     * @param bool $checked
     *
     * @return $this
     */
    public function withChecked(bool $checked)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param bool $available
     *
     * @return $this
     */
    public function withAvailable(bool $available)
    {
        $this->available = $available;

        return $this;
    }

}
