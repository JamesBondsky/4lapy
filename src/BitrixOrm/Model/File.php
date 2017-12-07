<?php

namespace FourPaws\BitrixOrm\Model;

use Bitrix\Main\Config\Option;
use Bitrix\Main\FileTable;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\Interfaces\FileInterface;

/**
 * Class File
 *
 * @package FourPaws\BitrixOrm\Model
 */
class File implements FileInterface
{
    /**
     * @var array
     */
    protected $fields;
    
    /**
     * @var string
     */
    protected $src;
    
    /**
     * File constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }
    
    /**
     * @return string
     */
    public function getSrc() : string
    {
        if ($this->src === null) {
            $src = sprintf('/%s/%s/%s',
                           Option::get('main', 'upload_dir', 'upload'),
                           $this->getSubDir(),
                           $this->getFileName());
            $this->setSrc($src);
        }
        
        return $this->src;
    }
    
    /**
     * @param string $src
     *
     * @return static
     */
    public function setSrc(string $src) : self
    {
        $this->src = $src;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getSubDir() : string
    {
        return (string)$this->fields['SUBDIR'];
    }
    
    /**
     * @return string
     */
    public function getFileName() : string
    {
        return (string)$this->fields['FILE_NAME'];
    }
    
    /**
     * @return int
     */
    public function getId() : int
    {
        return (int)$this->fields['ID'];
    }
    
    /**
     * @param int $id
     *
     * @return static
     */
    public function setId(int $id) : self
    {
        $this->fields['ID'] = $id;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->getSrc();
    }
    
    /**
     * @param string $primary
     *
     * @return mixed
     *
     * @throws FileNotFoundException
     */
    public static function createFromPrimary(string $primary)
    {
        $fields = FileTable::getById($primary)->fetch();
        
        if (!$fields) {
            throw new FileNotFoundException(sprintf('File with id %s is not found', $primary));
        }
        
        return new static($fields);
    }
}
