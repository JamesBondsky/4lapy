<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\SerializationVisitor;

use JMS\Serializer\Context;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * Class BlankSerializationVisitor
 * @package FourPaws\MobileApiBundle\SerializationVisitor
 */
class BlankSerializationVisitor extends JsonSerializationVisitor
{
    /**
     * {@inheritDoc}
     */
    public function visitProperty(PropertyMetadata $metadata, $data, Context $context): void
    {
        parent::visitProperty($metadata, $data, $context);
        $k = $this->namingStrategy->translateName($metadata);
        if (!$this->hasData($k)) {
            $this->setBlankValue($k, $metadata->type, $context);
        }
    }

    /**
     *
     *
     * @param string $k
     * @param array $type
     * @param Context $context
     *
     */
    protected function setBlankValue(string $k, array $type, Context $context)
    {
        switch ($type['name']) {
            case 'NULL':
                $this->setData($k, $this->visitNull(null, $type, $context));
                break;

            case 'string':
                $this->setData($k, $this->visitString('', $type, $context));
                break;
            case 'int':
            case 'integer':
                $this->setData($k, $this->visitInteger(0, $type, $context));
                break;
            case 'bool':
            case 'boolean':
                $this->setData($k, $this->visitBoolean(false, $type, $context));
                break;
            case 'double':
            case 'float':
                $this->setData($k, $this->visitDouble(0, $type, $context));
                break;
            case 'array':
                $this->setData($k, $this->visitArray([], $type, $context));
                break;
        }
    }
}
