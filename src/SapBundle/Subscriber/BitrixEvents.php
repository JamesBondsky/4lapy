<?php

namespace FourPaws\SapBundle\Subscriber;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\EventManager;
use FourPaws\App\Application;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\SapBundle\ReferenceDirectory\SapReferenceStorage;

class BitrixEvents implements ServiceHandlerInterface
{
    /**
     * Инициализация всех обработчиков сервиса
     *
     * @param \Bitrix\Main\EventManager $eventManager
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return mixed
     */
    public static function initHandlers(EventManager $eventManager)
    {
        /**
         * @var DataManager $dataManager
         */
        $referenceStorage = Application::getInstance()->getContainer()->get(SapReferenceStorage::class);
        foreach ($referenceStorage->getReferenceRepositoryRegistry()->getCollection() as $code => $referenceRepository) {
            /**
             * @todo fix DataManager events
             */
//            $entity = $dataManager::getEntity();
//            $eventManager->addEventHandler(
//                $entity->getModule(),
//                static::compileEventName($entity, DataManager::EVENT_ON_AFTER_ADD),
//                function () use ($code, $referenceStorage) {
//                    $referenceStorage->clear($code);
//                }
//            );
//            $eventManager->addEventHandler(
//                $entity->getModule(),
//                static::compileEventName($entity, DataManager::EVENT_ON_AFTER_UPDATE),
//                function () use ($code, $referenceStorage) {
//                    $referenceStorage->clear($code);
//                }
//            );
//            $eventManager->addEventHandler(
//                $entity->getModule(),
//                static::compileEventName($entity, DataManager::EVENT_ON_AFTER_DELETE),
//                function () use ($code, $referenceStorage) {
//                    $referenceStorage->clear($code);
//                }
//            );
        }
        return $eventManager;
    }

    protected static function compileEventName(Base $entity, $type)
    {
        return $entity->getNamespace() . $entity->getName() . '::' . $type;
    }
}
