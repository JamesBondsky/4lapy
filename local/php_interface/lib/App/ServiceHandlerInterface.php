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
}