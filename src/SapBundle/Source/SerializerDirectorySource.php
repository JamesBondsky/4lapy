<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Source;

use JMS\Serializer\SerializerInterface;
use RuntimeException;
use Symfony\Component\Finder\Finder;

class SerializerDirectorySource extends DirectorySource
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $format;

    /**
     * SerializerDirectorySource constructor.
     *
     * @param SerializerInterface $serializer
     * @param string $format
     * @param Finder $inFinder
     * @param string $type
     * @param $success
     * @param $error
     *
     * @throws RuntimeException
     */
    public function __construct(Finder $inFinder, $type, $success, $error, SerializerInterface $serializer, string $format = 'xml')
    {
        parent::__construct($inFinder, $type, $success, $error);
        $this->serializer = $serializer;
        $this->format = $format;
    }

    protected function convert($data)
    {
        return $this->serializer->deserialize($data, $this->type, $this->format);
    }
}
