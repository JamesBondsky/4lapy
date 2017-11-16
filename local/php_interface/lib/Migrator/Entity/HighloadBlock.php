<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Exception;
use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;
use FourPaws\Migrator\Exception\MigratorException;

/**
 * Class HighloadBlock
 *
 * @package FourPaws\Migrator\Entity
 */
abstract class HighloadBlock extends AbstractEntity
{
    private $highloadBlockCode = '';
    
    /**
     * @var DataManager
     */
    private $dataClass;
    
    /**
     * @param string $highloadBlockCode
     */
    private function setHighloadBlockCode(string $highloadBlockCode)
    {
        $this->highloadBlockCode = $highloadBlockCode;
    }
    
    /**
     * HighloadBlock constructor.
     *
     * @param string $entity
     * @param string $highloadBlockCode
     */
    public function __construct(string $entity, string $highloadBlockCode)
    {
        $this->setHighloadBlockCode($highloadBlockCode);
        parent::__construct($entity);
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return AddResult
     *
     * @throws AddException
     * @throws Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $result = null;
        
        try {
            $result = $this->getDataClass()->add($data);
        } catch (Exception $e) {
        }
        
        if (null === $result || !$result->getId()) {
            $message = $e ? $e->getMessage() : implode(', ', $result->getErrorMessages());
            
            throw new AddException(sprintf('Highloadblock %s element #%s add error: %s',
                                           $this->getHighloadBlockCode(),
                                           $primary,
                                           $message));
        }
        
        MapTable::addEntity($this->entity, $primary, $result->getId());
        
        return new AddResult(true, $result->getId());
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return UpdateResult
     * @throws UpdateException
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        $result = null;
        
        try {
            $result = $this->getDataClass()->update($primary, $data);
        } catch (Exception $e) {
        }
        
        if (null === $result || !$result->getId()) {
            $message = $e ? $e->getMessage() : implode(', ', $result->getErrorMessages());
            
            throw new UpdateException(sprintf('Highloadblock %s element #%s add error: %s',
                                              $this->getHighloadBlockCode(),
                                              $primary,
                                              $message));
        }
        
        return new UpdateResult(true, $result->getId());
    }
    
    /**
     * @return string
     */
    public function getHighloadBlockCode() : string
    {
        return $this->highloadBlockCode;
    }
    
    /**
     * @return DataManager
     *
     * @throws ArgumentException
     * @throws MigratorException
     * @throws SystemException
     */
    protected function getDataClass() : DataManager
    {
        if ($this->dataClass) {
            return $this->dataClass;
        }
        
        $table = HighloadBlockTable::getList(['filter' => ['=NAME' => $this->getHighloadBlockCode()]])->fetch();
        
        if (!$table) {
            throw new MigratorException(sprintf('Highloadblock with name %s is not found.',
                                                $this->getHighloadBlockCode()));
        }
        
        $dataClass = HighloadBlockTable::compileEntity($table)->getDataClass();
        
        if (is_string($dataClass)) {
            $dataClass = new $dataClass();
        }
        
        /**
         * @var DataManager $dataClass
         */
        return $dataClass;
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return UpdateResult
     *
     * @throws UpdateException
     * @throws Exception
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        return $this->updateItem($primary, [$field => $value]);
    }
}
