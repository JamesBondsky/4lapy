<?php

namespace FourPaws\AppBundle\SerializationVisitor;

use JMS\Serializer\Context;

/**
 * @todo Visitor
 *
 * Class CsvSerializationVisitor
 * @package FourPaws\AppBundle\Serialization
 */
class CsvDeserializationVisitor// extends JMS\Serializer\GenericDeserializationVisitor\GenericDeserializationVisitor
{
    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param array $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed|void
     *
     * @throws NotSupportedException
     */
    public function visitArray($data, array $type, Context $context)
    {
        throw new NotSupportedException('Array is not implemented into the csv serialization');
    }

    /**
     * @todo configure csv parameters
     *
     * @param string $data
     *
     * @return array
     */
    public function decode($data = ''): array
    {
        return str_getcsv(
            $data,
            ';',
            '"',
            '\\'
        );
    }
}
