<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\SerializerInterface;

/**
 * Class ResultXmlFactory
 *
 * @package FourPaws\External\Manzana\Model
 */
class ResultXmlFactory
{
    const RESULT_WRAPPER_TEMPLATE = '<result>%s</result>';
    
    /**
     * @param SerializerInterface $serializer
     * @param string              $xml
     *
     * @return ContactResult
     */
    public static function getContactResultFromXml(SerializerInterface $serializer, string $xml) : ContactResult
    {
        return $serializer->deserialize(self::getWrappedXml($xml), ContactResult::class, 'xml');
    }
    
    /**
     * @param string $xml
     *
     * @return string
     */
    private static function getWrappedXml(string $xml) : string
    {
        return sprintf(self::RESULT_WRAPPER_TEMPLATE, $xml);
    }
}
