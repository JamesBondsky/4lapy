<?php

namespace FourPaws\Search;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Elastica\Client;
use Elastica\Document;
use Elastica\Result;
use FourPaws\Catalog\Model\Product;
use FourPaws\Search\Enum\DocumentType;
use FourPaws\Search\Model\HitMetaInfo;
use InvalidArgumentException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;

class Factory
{
    const ENV_HOST = 'ELS_HOST';

    const ENV_PORT = 'ELS_PORT';

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $configParams
     *
     * @return Client
     * @throws RuntimeException
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

    /**
     * @param Product $product
     *
     * @return Document
     */
    public function makeProductDocument(Product $product)
    {
        return new Document(
            $product->getId(),
            $this->serializer->serialize(
                $product,
                'json',
                SerializationContext::create()->setGroups(['elastic'])
            ),
            DocumentType::PRODUCT
        );
    }

    /**
     * @param Result $result
     *
     * @return Product
     * @throws RuntimeException
     */
    public function makeProductObject(Result $result): Product
    {
        if (DocumentType::PRODUCT !== $result->getType()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Ожидается тип документа `%s` , а получен `%s`',
                    DocumentType::PRODUCT,
                    $result->getType()
                )
            );
        }

        $source = json_encode($result->getSource());

        $product = $this->serializer->deserialize(
            $source,
            Product::class,
            'json',
            DeserializationContext::create()->setGroups(['elastic'])
        );

        if (!($product instanceof Product)) {
            throw new RuntimeException('Ошибка десериализации продукта');
        }

        $product->withHitMetaInfo(HitMetaInfo::create($result));

        return $product;
    }

}
