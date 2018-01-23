<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class ReferralCardAttachResult
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @inheritdoc
 */
class ReferralCardAttachResult extends Result
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("contactid")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Accessor(getter="getContactId", setter="setContactId")
     */
    protected $contactId = '';
    
    /**
     * @return string
     */
    public function getContactId() : string
    {
        return $this->contactId;
    }
    
    /**
     * @param string $contactId
     */
    public function setContactId(string $contactId = '')
    {
        $this->result = $contactId;
    }
    
    /**
     * @param string $result
     */
    public function setResult(string $result = '')
    {
        $this->error = true;
        
        parent::setResult($result);
    }
}
