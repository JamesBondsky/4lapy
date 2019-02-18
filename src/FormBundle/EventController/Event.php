<?php

namespace FourPaws\FormBundle\EventController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\EventManager;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Tools\StaticLoggerTrait;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\FormBundle\EventController
 */
class Event extends BaseServiceHandler
{
    use StaticLoggerTrait;

    protected static $ORDER_FEEDBACK = 'FORM_FILLING_order_feedback';

    protected static $isEventsDisable = false;

    public static function disableEvents(): void
    {
        self::$isEventsDisable = true;
    }

    public static function enableEvents(): void
    {
        self::$isEventsDisable = false;
    }

    /**
     * @param EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        $module = 'main';

        /** обратная связь по заказам */
        AddEventHandler($module, 'OnBeforeEventAdd', [self::class, "orderFeedback"]);
    }

    public function orderFeedback(&$event, &$lid, &$arFields, &$messageId, &$files, &$languageId): void
    {
        if($event == self::$ORDER_FEEDBACK)
        {
            /** @var CurrentUserProviderInterface $user */
            $userService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            $arFields['PHONE'] = $userService->getCurrentUser()->getPersonalPhone();
        }
    }
}
