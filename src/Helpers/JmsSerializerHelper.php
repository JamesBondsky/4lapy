<?php
/**
 * Created by PhpStorm.
 * Date: 02.04.2018
 * Time: 17:32
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\Helpers;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;


/**
 * Class JmsSerializerHelper
 * @package FourPaws\Helpers
 */
class JmsSerializerHelper
{
    /**
     * @return Serializer
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public static function getJmsSerializer(): Serializer
    {
        $container = Application::getInstance()->getContainer();

        $serializerBuilder = new SerializerBuilder();
        $nameStrategy = $container->get('jms_serialized_name_annotation_strategy');
        $classes = ClassFinderHelper::getClasses("FourPaws\\AppBundle\\SerializationVisitor\\",
            \dirname(Application::getDocumentRoot() . '\..') . '/src/AppBundle/SerializationVisitor');
        foreach ($classes as $class) {
            if (method_exists($class, 'getFormat')) {
                $serializerBuilder->setSerializationVisitor($class::getFormat(), new $class($nameStrategy));
            }
        }
        $classes = ClassFinderHelper::getClasses("FourPaws\\AppBundle\\DeserializationVisitor\\",
            \dirname(Application::getDocumentRoot() . '\..') . '/src/AppBundle/DeserializationVisitor');
        foreach ($classes as $class) {
            if (method_exists($class, 'getFormat')) {
                $serializerBuilder->setDeserializationVisitor($class::getFormat(), new $class($nameStrategy));
            }
        }
        $serializerBuilder->configureHandlers(function (HandlerRegistry $registry) {
            $classes = ClassFinderHelper::getClasses("FourPaws\\AppBundle\\Serialization\\",
                \dirname(Application::getDocumentRoot() . '\..') . '/src/AppBundle/Serialization');
            foreach ($classes as $class) {
                $registry->registerSubscribingHandler(new $class());
            }
        });
        return $serializerBuilder->build();
    }
}