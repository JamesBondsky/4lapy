<?php

namespace FourPaws\Search;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Elastica\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;

class Factory
{
    const ENV_HOST = 'ELS_HOST';

    const ENV_PORT = 'ELS_PORT';

    /**
     * @param array $configParams
     *
     * @return Client
     */
    public function createElasticaClient(array $configParams = []): Client
    {
        $host = getenv(self::ENV_HOST);
        if (false === $host) {
            throw new EnvNotFoundException(self::ENV_HOST);
        }

        $port = getenv(self::ENV_PORT);
        if (false === $host) {
            throw new EnvNotFoundException(self::ENV_HOST);
        }

        /** @var Logger $logger */
        $logger = LoggerFactory::create('ElasticaClient');

        /*
         * Повышаем всем уровень логирования,
         * чтобы отладочные сообщения не забивали
         * лог на dev зонах
         */
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof StreamHandler) {
                $handler->setLevel(Logger::INFO);
            }
        }

        $client = new Client(
            ['host' => $host, 'port' => $port],
            null,
            $logger
        );

        foreach ($configParams as $paramPair) {
            foreach ($paramPair as $key => $value) {
                $client->setConfigValue($key, $value);
            }
        }

        return $client;
    }

}
