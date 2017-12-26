<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Result
 *
 * @package FourPaws\External\Manzana\Model
 * @Serializer\XmlRoot("result")
 */
class Result
{
    /**
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Accessor(getter="getResult", setter="setResult")
     */
    protected $result;
    
    /**
     * @Serializer\Exclude()
     */
    protected $error = false;
    
    /**
     * @return string
     */
    public function getResult() : string
    {
        return $this->result;
    }
    
    /**
     * @param string $result
     */
    public function setResult(string $result = '')
    {
        $this->result = $result;
    }
    
    /**
     * @return bool
     */
    public function isError() : bool
    {
        return $this->error;
    }
}
