<?php

namespace FourPaws\BitrixOrm\Model;

use Adv\Bitrixtools\Tools\BitrixUtils;

/**
 * Class BitrixArrayItemBase
 *
 * Из-за того, что JMS-serializer не видит трейты и может использоваться не везде, где будет использоваться BitrixOrm,
 * а сам Битрикс числа и все остальные типы данных возвращает как строки, решено:
 *
 * 1 Снабдить модельные классы BitrixOrm явными аннотациями для строгого соблюдения типа.
 *
 * 2 В таких аннотациях использовать полное имя класса аннотации, чтобы не требовать обязательной установки пакета
 * jms/serializer для работоспособности.
 *
 * 3 При использовании трейта дублировать свойства с аннотацией типа.
 *
 * @package FourPaws\BitrixOrm\Model
 *
 */
abstract class BitrixArrayItemBase implements ModelInterface
{
    const PATTERN_PROPERTY_VALUE = '~^(?>(PROPERTY_\w+)_VALUE)$~';

    /**
     * @var bool
     * @JMS\Serializer\Annotation\Type("bool")
     */
    protected $active = true;

    /**
     * @var int
     * @JMS\Serializer\Annotation\Type("int")
     */
    protected $ID = 0;

    /**
     * @var string
     * @JMS\Serializer\Annotation\Type("string")
     */
    protected $XML_ID = '';

    /**
     * BitrixArrayItemBase constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        foreach ($fields as $field => $value) {
            if ($this->isExists($field)) {
                $this->{$field} = $value;
            } elseif ($this->isProperty($field)) {
                $propertyName = $this->getPropertyName($field);

                if ($this->isExists($propertyName)) {
                    $this->{$propertyName} = $value;
                }
            }
        }

        if (isset($fields['ACTIVE'])) {
            $this->withActive(BitrixUtils::bitrixBool2bool($fields['ACTIVE']));
        }
    }

    public function toArray()
    {
        $result = [];
        //TODO Дописать лучше часть про поля
        foreach (get_object_vars($this) as $field => $value) {
            if ('PROPERTY_' === substr($field, 0, 9)) {
                $result['PROPERTY_VALUES'][substr($field, 9)] = $value;
            } elseif (!is_object($value) && !is_null($value)) {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    /**
     * @param string $fieldName
     *
     * @return string
     */
    protected function getPropertyName(string $fieldName): string
    {
        return preg_replace(self::PATTERN_PROPERTY_VALUE, '$1', $fieldName);
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    protected function isProperty(string $fieldName): bool
    {
        return preg_match(self::PATTERN_PROPERTY_VALUE, $fieldName) > 0;
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    protected function isExists(string $fieldName): bool
    {
        return property_exists($this, $fieldName);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->ID;
    }

    /**
     * @param int $ID
     *
     * @return $this
     */
    public function withId(int $ID)
    {
        $this->ID = $ID;

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->XML_ID;
    }

    /**
     * @param string $XML_ID
     *
     * @return $this
     */
    public function withXmlId(string $XML_ID)
    {
        $this->XML_ID = $XML_ID;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function withActive(bool $active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function createFromPrimary(string $primary): ModelInterface
    {
        /**
         * @todo Заглушка. Удалить после реализации создания в более конкретных классах.
         */
    }

}
