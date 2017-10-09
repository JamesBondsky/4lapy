<?php

namespace FourPaws\App;

final class EventManager
{
    private $classesDir  = '';
    
    private $classesList = [];
    
    /**
     * @param \Bitrix\Main\EventManager $eventManager
     *
     * @return \FourPaws\App\EventHandlerInterface[]
     */
    private function getClassesList(\Bitrix\Main\EventManager $eventManager) : array
    {
        if (!$this->classesList) {
            $this->scan($eventManager);
        }
        
        return $this->classesList;
    }
    
    /**
     * Relative path
     *
     * @param string $classesDir
     */
    private function setClassesDir(string $classesDir)
    {
        $this->classesDir = $classesDir;
    }
    
    /**
     * EventManager constructor.
     *
     * @param string $classesDir
     */
    public function __construct(string $classesDir)
    {
        $this->setClassesDir($classesDir);
    }
    
    /**
     * @param \Bitrix\Main\EventManager $eventManager
     */
    private function scan(\Bitrix\Main\EventManager $eventManager)
    {
        foreach (glob(__DIR__ . '/' . $this->classesDir . '/*.php') as $file) {
            $dirLength = strlen(__DIR__ . '/' . $this->classesDir) + 1;
            $className = substr($file,
                                $dirLength,
                                strlen($file) - $dirLength - 4);
            $className = '\\' . __NAMESPACE__ . '\\' . ucfirst($this->classesDir) . '\\' . $className;
            
            $this->classesList[] = new $className($eventManager);
        }
    }
    
    /**
     * @param \Bitrix\Main\EventManager $eventManager
     */
    public function handleEvents(\Bitrix\Main\EventManager $eventManager)
    {
        foreach ($this->getClassesList($eventManager) as $object) {
            $object->handleEvents();
        }
    }
}