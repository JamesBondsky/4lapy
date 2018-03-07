<?php

namespace FourPaws\Helpers;

use FourPaws\AppBundle\Serialization\ArrayCommaString;
use FourPaws\AppBundle\Serialization\ArrayOrFalseHandler;
use FourPaws\AppBundle\Serialization\BitrixBooleanHandler;
use FourPaws\AppBundle\Serialization\BitrixDateHandler;
use FourPaws\AppBundle\Serialization\BitrixDateTimeHandler;
use FourPaws\AppBundle\Serialization\ManzanaDateTimeImmutableFullShortHandler;
use FourPaws\AppBundle\Serialization\PhoneHandler;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

/**
 * Class SerializerHelper
 *
 * @package FourPaws\Helpers
 */
class SerializerHelper
{
    /**
     * @return Serializer
     * @throws RuntimeException
     */
    public static function get(): Serializer
    {
        return SerializerBuilder::create()->configureHandlers(
            function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new ArrayCommaString());
                $registry->registerSubscribingHandler(new ArrayOrFalseHandler());
                $registry->registerSubscribingHandler(new BitrixBooleanHandler());
                $registry->registerSubscribingHandler(new BitrixDateHandler());
                $registry->registerSubscribingHandler(new BitrixDateTimeHandler());
                $registry->registerSubscribingHandler(new ManzanaDateTimeImmutableFullShortHandler());
                $registry->registerSubscribingHandler(new PhoneHandler());
            }
        )->build();
    }
}
