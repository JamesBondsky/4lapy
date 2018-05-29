<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Subscriber;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\EventManager;
use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\SapBundle\ReferenceDirectory\SapReferenceStorage;

class BitrixEvents extends BaseServiceHandler
{
    /**
     * @var Collection
     */
    private $collection;

    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }

    /**
     * Инициализация всех обработчиков сервиса
     *
     * @param EventManager $eventManager
     *
     * @throws ArgumentException
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        /**
         * @var static $current
         */
        $current = Application::getInstance()->getContainer()->get(self::class);
        $referenceStorage = Application::getInstance()->getContainer()->get(SapReferenceStorage::class);

        /**
         * @var DataManager $dataManager
         */
        foreach ($current->getCollection() as $code => $dataManager) {
            $entity = $dataManager::getEntity();

            static::initHandler(static::compileEventName($entity, DataManager::EVENT_ON_AFTER_ADD),
                function () use ($code, $referenceStorage) {
                    $referenceStorage->clear($code);
                }, $entity->getModule());

            static::initHandler(static::compileEventName($entity, DataManager::EVENT_ON_AFTER_UPDATE),
                function () use ($code, $referenceStorage) {
                    $referenceStorage->clear($code);
                }, $entity->getModule());

            static::initHandler(static::compileEventName($entity, DataManager::EVENT_ON_AFTER_DELETE),
                function () use ($code, $referenceStorage) {
                    $referenceStorage->clear($code);
                }, $entity->getModule());

            $eventManager->addEventHandler(
                $entity->getModule(),
                static::compileEventName($entity, DataManager::EVENT_ON_AFTER_UPDATE),
                function () use ($code, $referenceStorage) {
                    $referenceStorage->clear($code);
                }
            );
        }
    }

    /**
     * @param Base $entity
     * @param      $type
     *
     * @return string
     */
    protected static function compileEventName(Base $entity, $type): string
    {
        return $entity->getNamespace() . $entity->getName() . '::' . $type;
    }

    /**
     * @param string      $code
     * @param DataManager $dataManager
     */
    public function add(string $code, DataManager $dataManager)
    {
        return $this->collection->set($code, $dataManager);
    }

    /**
     * @return Collection|DataManager[]
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }
}
