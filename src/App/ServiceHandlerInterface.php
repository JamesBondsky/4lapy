<?php

namespace FourPaws\App;

use Bitrix\Main\EventManager;

interface ServiceHandlerInterface
{
    /**
     * Инициализация всех обработчиков сервиса
     *
     * @param \Bitrix\Main\EventManager $eventManager
     */
    public static function initHandlers(EventManager $eventManager): void;
}
