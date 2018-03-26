<?php

namespace FourPaws\App;

use Bitrix\Main\EventManager;

/**
 * Interface ServiceHandlerInterface
 *
 * @package FourPaws\App
 */
interface ServiceHandlerInterface
{
    /**
     * Инициализация всех обработчиков сервиса
     *
     * @param \Bitrix\Main\EventManager $eventManager
     */
    public static function initHandlers(EventManager $eventManager): void;
}
