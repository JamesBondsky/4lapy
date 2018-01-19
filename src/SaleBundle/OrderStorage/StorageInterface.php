<?php

namespace FourPaws\SaleBundle\OrderStorage;

use Doctrine\Common\Collections\ArrayCollection;

interface StorageInterface
{
    /**
     * Инициализация хранилища
     *
     * @param int|null $fuserId
     *
     * @return StorageInterface|bool
     */
    public static function create(int $fuserId = null);

    /**
     * Очистка хранилища
     *
     * @param int|null $fuserId
     *
     * @return bool
     */
    public static function clear(int $fuserId = null): bool;

    /**
     * Сохранение данных хранилища
     *
     * @return bool
     */
    public function save(): bool;

    /**
     * Получение всех сохраненных полей заказа
     *
     * @return ArrayCollection
     */
    public function getFields(): ArrayCollection;

    /**
     * Получение поля заказа по коду
     *
     * @param $code
     *
     * @return mixed
     */
    public function getField($code);

    /**
     * Получение всех сохраненных свойств заказа
     *
     * @return ArrayCollection
     */
    public function getProperties(): ArrayCollection;

    /**
     * Получение свойства заказа по коду
     *
     * @param $code
     *
     * @return Field|bool
     */
    public function getProperty($code);

    /**
     * Задание значения полю заказа
     *
     * @param $code
     * @param $value
     *
     * @return bool
     */
    public function setField($code, $value): bool;

    /**
     * Задание значения свойству заказа
     *
     * @param $code
     * @param $value
     *
     * @return mixed
     */
    public function setProperty($code, $value): bool;
}
