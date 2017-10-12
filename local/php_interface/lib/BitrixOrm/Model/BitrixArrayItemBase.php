<?php

namespace FourPaws\BitrixOrm\Model;

use Adv\Bitrixtools\Tools\BitrixUtils;

abstract class BitrixArrayItemBase implements ModelInterface
{
    const PATTERN_PROPERTY_VALUE = '~^(?>(PROPERTY_\w+)_VALUE)$~';
    
    /**
     * @var bool
     */
    protected $active = true;
    
    /**
     * @var int
     */
    protected $ID = 0;
    
    /**
     * @var string
     */
    protected $XML_ID = '';
    
    /**
     * BitrixArrayItemBase constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        foreach ($fields as $field => $value) {
            if ($this->isExists($field)) {
                $this->{$field} = $value;
            } elseif ($this->isProperty($field)) {
                $propertyName = $this->getPropertyName($field);
                
                if ($this->isExists($propertyName)) {
                    $this->{$propertyName} = $value;
                }
            }
        }
        
        if (isset($fields['ACTIVE'])) {
            $this->withActive(BitrixUtils::bitrixBool2bool($fields['ACTIVE']));
        }
    }
    
    /**
     * @param string $fieldName
     *
     * @return string
     */
    protected function getPropertyName(string $fieldName) : string
    {
        return preg_replace(self::PATTERN_PROPERTY_VALUE, '$1', $fieldName);
    }
    
    /**
     * @param string $fieldName
     *
     * @return bool
     */
    protected function isProperty(string $fieldName) : bool
    {
        return preg_match(self::PATTERN_PROPERTY_VALUE, $fieldName) > 0;
    }
    
    /**
     * @param string $fieldName
     *
     * @return bool
     */
    protected function isExists(string $fieldName) : bool
    {
        return property_exists($this, $fieldName);
    }
    
    /**
     * @return int
     */
    public function getId() : int
    {
        return (int)$this->ID;
    }
    
    /**
     * @param int $ID
     *
     * @return $this
     */
    public function withId(int $ID)
    {
        $this->ID = $ID;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getXmlId() : string
    {
        return $this->XML_ID;
    }
    
    /**
     * @param string $XML_ID
     *
     * @return $this
     */
    public function withXmlId(string $XML_ID)
    {
        $this->XML_ID = $XML_ID;
        
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->active;
    }
    
    /**
     * @param bool $active
     *
     * @return $this
     */
    public function withActive(bool $active)
    {
        $this->active = $active;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public static function createFromPrimary(int $primary) : ModelInterface
    {
        /**
         * @todo Заглушка. Удалить после реализации создания в более конкретных классах.
         */
    }
    
}
