<?php

namespace FourPaws\Catalog;

use Adv\Bitrixtools\Tools\EnvType;
use FourPaws\App\Application;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

class Factory
{
    /**
     * @return Serializer
     */
    public static function buildSerializer(): Serializer
    {
        //TODO Переделать на сервис и настроить через YML
        return SerializerBuilder::create()
                                ->setCacheDir(
                                    Application::getAbsolutePath(Application::BITRIX_CACHE_DIR . '/jms-serializer')
                                )
                                ->setDebug(EnvType::isDev())
                                ->setPropertyNamingStrategy(new IdenticalPropertyNamingStrategy())
                                ->build();
    }

}
