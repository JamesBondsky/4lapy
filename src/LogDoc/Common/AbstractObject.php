<?php

namespace FourPaws\LogDoc\Common;

abstract class AbstractObject implements \ArrayAccess, \JsonSerializable
{
    /**
     * @var array Свойства установленные пользователем
     */
    private $unknownProperties = [];

    /**
     * Проверяет наличие свойства
     * @param string $offset Имя проверяемого свойства
     * @return bool True если свойство имеется, false если нет
     */
    public function offsetExists($offset)
    {
        $method = 'get' . ucfirst($offset);
        if (method_exists($this, $method)) {
            return true;
        }
        $method = 'get' . self::matchPropertyName($offset);
        if (method_exists($this, $method)) {
            return true;
        }

        return array_key_exists($offset, $this->unknownProperties);
    }

    /**
     * Возвращает значение свойства
     * @param string $offset Имя свойства
     * @return mixed Значение свойства
     */
    public function offsetGet($offset)
    {
        $method = 'get' . ucfirst($offset);
        if (method_exists($this, $method)) {
            return $this->{$method} ();
        }
        $method = 'get' . self::matchPropertyName($offset);
        if (method_exists($this, $method)) {
            return $this->{$method} ();
        }

        return array_key_exists($offset, $this->unknownProperties) ? $this->unknownProperties[$offset] : null;
    }

    /**
     * Устанавливает значение свойства
     * @param string $offset Имя свойства
     * @param mixed $value Значение свойства
     */
    public function offsetSet($offset, $value)
    {
        $method = 'set' . ucfirst($offset);
        if (method_exists($this, $method)) {
            $this->{$method} ($value);
        } else {
            $method = 'set' . self::matchPropertyName($offset);
            if (method_exists($this, $method)) {
                $this->{$method} ($value);
            } else {
                $this->unknownProperties[$offset] = $value;
            }
        }
    }

    /**
     * Удаляет свойство
     * @param string $offset Имя удаляемого свойства
     */
    public function offsetUnset($offset)
    {
        $method = 'set' . ucfirst($offset);
        if (method_exists($this, $method)) {
            $this->{$method} (null);
        } else {
            $method = 'set' . self::matchPropertyName($offset);
            if (method_exists($this, $method)) {
                $this->{$method} (null);
            } else {
                unset($this->unknownProperties[$offset]);
            }
        }
    }

    /**
     * Возвращает значение свойства
     * @param string $propertyName Имя свойства
     * @return mixed Значение свойства
     */
    public function __get($propertyName)
    {
        return $this->offsetGet($propertyName);
    }

    /**
     * Устанавливает значение свойства
     * @param string $propertyName Имя свойства
     * @param mixed $value Значение свойства
     */
    public function __set($propertyName, $value)
    {
        $this->offsetSet($propertyName, $value);
    }

    /**
     * Проверяет наличие свойства
     * @param string $propertyName Имя проверяемого свойства
     * @return bool True если свойство имеется, false если нет
     */
    public function __isset($propertyName)
    {
        return $this->offsetExists($propertyName);
    }

    /**
     * Удаляет свойство
     * @param string $propertyName Имя удаляемого свойства
     */
    public function __unset($propertyName)
    {
        $this->offsetUnset($propertyName);
    }

    /**
     * Устанавливает значения свойств текущего объекта из массива
     * @param array|\Traversable $sourceArray Ассоциативный массив с найтройками
     */
    public function fromArray($sourceArray)
    {
        foreach ($sourceArray as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    /**
     * Возвращает ассоциативный массив со свойствами текущего объекта для его дальнейшей JSON сериализации
     * @return array Ассоциативный массив со свойствами текущего объекта
     */
    public function jsonSerialize()
    {
        $result = array();
        foreach (get_class_methods($this) as $method) {
            if (strncmp('get', $method, 3) === 0) {
                if ($method === 'getUnknownProperties') {
                    continue;
                }
                $property = strtolower(preg_replace('/[A-Z]/', '_\0', lcfirst(substr($method, 3))));
                $value = $this->serializeValueToJson($this->{$method} ());
                if ($value !== null) {
                    $result[$property] = $value;
                }
            }
        }
        if (!empty($this->unknownProperties)) {
            foreach ($this->unknownProperties as $property => $value) {
                if (!array_key_exists($property, $result)) {
                    $result[$property] = $this->serializeValueToJson($value);
                }
            }
        }

        return $result;
    }

    private function serializeValueToJson($value)
    {
        if ($value === null || is_scalar($value) || is_array($value)) {
            return $value;
        } elseif (is_object($value) && $value instanceof \JsonSerializable) {
            return $value->jsonSerialize();
        } elseif (is_object($value) && $value instanceof \DateTime) {
            return $value->format(DATE_ATOM);
        }

        return $value;
    }

    /**
     * Возвращает массив свойств которые не существуют, но были заданы у объекта
     * @return array Ассоциативный массив с не существующими у текущего объекта свойствами
     */
    protected function getUnknownProperties()
    {
        return $this->unknownProperties;
    }

    /**
     * Преобразует имя свойства из snake_case в camelCase
     * @param string $property Преобразуемое значение
     * @return string Значение в камэл кейсе
     */
    private static function matchPropertyName($property)
    {
        return preg_replace('/\_(\w)/', '\1', $property);
    }
}
