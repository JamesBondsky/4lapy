<?php

namespace FourPaws\User;

use Bitrix\Main\EventManager;
use FourPaws\App\Application;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;

/**
 * Class UserServiceHandlers
 *
 * Обработчики событий
 *
 * @package FourPaws\User
 * @todo    Почему этот класс абстрактный? оО что за магия
 */
abstract class UserServiceHandlers implements ServiceHandlerInterface
{
    /**
     * @var EventManager
     */
    protected static $eventManager;
    
    /**
     * @param \Bitrix\Main\EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager)
    {
        self::$eventManager = $eventManager;
        
        self::initHandler('OnBeforeUserAdd', 'checkSocserviseRegisterHandler');
        
        self::initHandler('OnBeforeUserLogon', 'replaceLogin');
    }
    
    /**
     * @param string $eventName
     * @param string $method
     * @param string $module
     */
    public static function initHandler(string $eventName, string $method, string $module = 'main')
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            [
                self::class,
                $method,
            ]
        );
    }
    
    /**
     * @param array $fields
     *
     * @return bool|void
     */
    public static function checkSocserviseRegisterHandler(array &$fields)
    {
        if ($fields['EXTERNAL_AUTH_ID'] === 'socservices') {
            /** Установка обязательных пользовательских полей */
            $fields['UF_CONFIRMATION'] = 1;
        }
    }
    
    /**
     * @param array $fields
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public static function replaceLogin(array $fields)
    {
        global $APPLICATION;
        $userService = Application::getInstance()
                                  ->getContainer()
                                  ->get(UserRegistrationProviderInterface::class);
        if (!empty($fields['LOGIN'])) {
            $fields['LOGIN'] = $userService->getLoginByRawLogin((string)$fields['LOGIN']);
        } else {
            $APPLICATION->ThrowException('Поле не может быть пустым');
        }
    }
}
