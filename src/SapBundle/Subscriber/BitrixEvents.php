<?php

namespace FourPaws\SapBundle\Subscriber;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\EventManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\SapBundle\ReferenceDirectory\SapReferenceStorage;

class BitrixEvents implements ServiceHandlerInterface
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
         * @var static $current
         */
        $current = Application::getInstance()->getContainer()->get(static::class);
        $referenceStorage = Application::getInstance()->getContainer()->get(SapReferenceStorage::class);

        /**
         * @var DataManager $dataManager
         */
        foreach ($current->getCollection() as $code => $dataManager) {
            $entity = $dataManager::getEntity();
            $eventManager->addEventHandler(
                $entity->getModule(),
                static::compileEventName($entity, DataManager::EVENT_ON_AFTER_ADD),
                function () use ($code, $referenceStorage) {
                    $referenceStorage->clear($code);
                }
            );
            $eventManager->addEventHandler(
                $entity->getModule(),
                static::compileEventName($entity, DataManager::EVENT_ON_AFTER_UPDATE),
                function () use ($code, $referenceStorage) {
                    $referenceStorage->clear($code);
                }
            );
            $eventManager->addEventHandler(
                $entity->getModule(),
                static::compileEventName($entity, DataManager::EVENT_ON_AFTER_DELETE),
                function () use ($code, $referenceStorage) {
                    $referenceStorage->clear($code);
                }
            );
        }
        return $eventManager;
    }

    protected static function compileEventName(Base $entity, $type)
    {
        return $entity->getNamespace() . $entity->getName() . '::' . $type;
    }

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
