<?php

namespace FourPaws\AppBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Result;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;

class FetchTransformer
{
    /**
     * @var ArrayTransformerInterface
     */
    private $transformer;

    public function __construct(ArrayTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param Result                      $result
     * @param string                      $class
     * @param null|DeserializationContext $context
     * @throws ArgumentException
     */
    public function addFetchDataModifier(Result $result, string $class, DeserializationContext $context = null)
    {
        $result->addFetchDataModifier(function (&$data) use ($class, $context) {
            $data = $this->transform($data, $class, $context);
            return $data;
        });
    }

    /**
     * @param                             $data
     * @param string                      $class
     * @param null|DeserializationContext $context
     * @return mixed
     */
    public function transform($data, string $class, DeserializationContext $context = null)
    {
        return $this->transformer->fromArray($data, $class, $context);
    }
}
