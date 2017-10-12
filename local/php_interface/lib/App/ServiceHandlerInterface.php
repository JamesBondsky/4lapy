<?php

namespace FourPaws\App;

use Bitrix\Main\EventManager;

interface ServiceHandlerInterface
{
    /**
     * Инициализация всех обработчиков сервиса
     *
     * @param \Bitrix\Main\EventManager $eventManager
     *
     * @return mixed
     */
    public static function initHandlers(EventManager $eventManager);

    /**
     * Инициализация отдельного обработчика
     *
     * @param string $eventName
     * @param string $method
     * @param string $module
     */
    public static function initHandler(string $eventName, string $method, string $module);
}