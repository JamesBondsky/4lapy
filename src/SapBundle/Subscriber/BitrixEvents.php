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
use FourPaws\SapBundle\ReferenceDirectory\SapReferenceStorage;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class BitrixEvents
 *
 * @package FourPaws\SapBundle\Subscriber
 */
class BitrixEvents extends BaseServiceHandler
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * BitrixEvents constructor.
     */
    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws RuntimeException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function initReferenceHandler(): void
    {
        /**
         * @var static $current
         */
        $eventManager = EventManager::getInstance();
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
