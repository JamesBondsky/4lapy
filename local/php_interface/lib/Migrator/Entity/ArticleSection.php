<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Entity\Exceptions\UpdateException;
use FourPaws\Migrator\Utils;

class ArticleSection extends IBlockSection
{
    private $isUpdated = false;
    
    public function setDefaults()
    {
        /**
         * У нас нет значений по умолчанию для этой сущности
         */
        return;
    }
    
    /**
     * ArticleSection constructor.
     *
     * @param string $entity
     * @param int    $iblockId
     */
    public function __construct($entity, $iblockId = 0)
    {
        if (!$iblockId) {
            $iblockId = Utils::getIblockId('publications', 'articles');
        }
        
        parent::__construct($entity, $iblockId);
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function addItem(string $primary, array $data) : Result
    {
        $result = parent::addItem($primary, $data);
        
        $this->isUpdated = true;
        
        return $result;
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function updateItem(string $primary, array $data) : Result
    {
        $result = parent::updateItem($primary, $data);
        
        $this->isUpdated = true;
        
        return $result;
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        $cIblockSection = new \CIBlockSection();
        
        if ($cIblockSection->Update($primary, [$field => $value])) {
            return new UpdateResult(true, $primary);
        }
        
        throw new UpdateException("Update field with primary {$primary} error: {$cIblockSection->LAST_ERROR}");
    }
    
    /**
     * Пересчитываем разделы ТОЛЬКО по окончании миграции
     */
    public function __destruct()
    {
        if ($this->isUpdated) {
            \CIBlockSection::ReSort($this->getIblockId());
        }
    }
}