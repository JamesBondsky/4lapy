<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\AppBundle\Construction;

use JMS\Serializer\Construction\UnserializeObjectConstructor as BaseUnserializeObjectConstructor;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\VisitorInterface;

class UnserializeObjectConstructor extends BaseUnserializeObjectConstructor
{
    public const CALL_CONSTRUCTOR = 'callConstructor';

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * {@inheritdoc}
     */
    public function construct(
        VisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ) {
        if (!$context->attributes->containsKey(static::CALL_CONSTRUCTOR) ||
            !$context->attributes->get(static::CALL_CONSTRUCTOR)->get()
        ) {
            return parent::construct($visitor, $metadata, $data, $type, $context);
        }
        $className = $metadata->name;

        return new $className();
    }

}
