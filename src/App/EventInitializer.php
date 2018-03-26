<?php

namespace FourPaws\App;

use Bitrix\Main\EventManager;
use FourPaws\CatalogBundle\EventController\Event as CatalogEvent;
use FourPaws\DeliveryBundle\EventController\Event as DeliveryEvent;
use FourPaws\IblockProps\Event as IblockPropsEvent;
use FourPaws\MobileApiBundle\EventController\Event as MobileApiUserEvent;
use FourPaws\ProductAutoSort\Event as ProductAutoSortEvent;
use FourPaws\SaleBundle\EventController\Event as SaleEvent;
use FourPaws\SapBundle\EventController\Event as SapEvent;
use FourPaws\SapBundle\Subscriber\BitrixEvents;
use FourPaws\Search\EventHandlers as SearchEventHandlers;
use FourPaws\UserBundle\EventController\Event as UserEvent;
use FourPaws\UserProps\Event as UserPropLocationEvent;
use FourPaws\PersonalBundle\EventController\Event as PersonalEvent;
use Generator;
use ReflectionException;
use RuntimeException;

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
        IblockPropsEvent::class,
        ProductAutoSortEvent::class,
        SaleEvent::class,
        DeliveryEvent::class,
        SapEvent::class,
        SearchEventHandlers::class,
        UserEvent::class,
        UserPropLocationEvent::class,
        BitrixEvents::class,
        MobileApiUserEvent::class,
        CatalogEvent::class,
        PersonalEvent::class,
    ];

    /**
     * Исполняем хендлеры
     *
     * @param EventManager $eventManager
     *
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public function __invoke(EventManager $eventManager)
    {
        foreach ($this->getServiceHandlerClassList() as $class) {
            $class::initHandlers($eventManager);
        }
    }

    /**
     * @throws ReflectionException
     * @throws RuntimeException
     * @return Generator
     */
    private function getServiceHandlerClassList(): Generator
    {
        foreach (self::SERVICE_HANDLER_CLASSES as $serviceHandlerClass) {
            $interfaces = (new \ReflectionClass($serviceHandlerClass))->getInterfaceNames();

            if (!\in_array(ServiceHandlerInterface::class, $interfaces, true)) {
                throw new RuntimeException('Handler class must be an instance of ServiceHandlerInterface');
            }

            yield $serviceHandlerClass;
        }
    }
}
