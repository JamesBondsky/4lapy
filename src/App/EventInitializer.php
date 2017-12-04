<?php

namespace FourPaws\App;

use Bitrix\Main\EventManager;
use FourPaws\ProductAutoSort\Event as ProductAutoSortEvent;
use FourPaws\IblockProps\Event as IblockPropsEvent;
use FourPaws\Search\Event as CatalogEvent;
use FourPaws\User\UserServiceHandlers;

/**
 * Class EventInitializer
 *
 * Инициализируем обработчики
 *
 * @package FourPaws\App
 */
final class EventInitializer
{
    const SERVICE_HANDLER_CLASSES = [
        UserServiceHandlers::class,
        ProductAutoSortEvent::class,
        CatalogEvent::class,
        IblockPropsEvent::class
    ];

    /**
     * Исполняем хендлеры
     *
     * @param \Bitrix\Main\EventManager $eventManager
     *
     * @throws \RuntimeException
     * @throws \ReflectionException
     */
    public function __invoke(EventManager $eventManager)
    {
        foreach ($this->getServiceHandlerClassList() as $class) {
            $class::initHandlers($eventManager);
        }
    }

    /**
     * @return \Generator
     *
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    private function getServiceHandlerClassList()
    {
        foreach (self::SERVICE_HANDLER_CLASSES as $serviceHandlerClass) {
            $interfaces = (new \ReflectionClass($serviceHandlerClass))->getInterfaceNames();

            if (!in_array(ServiceHandlerInterface::class, $interfaces, true)) {
                throw new \RuntimeException('Handler class must be an instance of ServiceHandlerInterface');
            }

            yield $serviceHandlerClass;
        }
    }
}
