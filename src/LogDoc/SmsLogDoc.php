<?php

namespace FourPaws\LogDoc;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Elastica\Client;
use FourPaws\App\Application;
use FourPaws\LogDoc\Handler\ElasticaAdapter;
use FourPaws\LogDoc\Handler\HandlerInterface;
use FourPaws\LogDoc\Model\Document;
use FourPaws\LogDoc\Model\DocumentInterface;
use FourPaws\LogDoc\Model\ResultInterface;
use Psr\Log\LoggerAwareInterface;

class SmsLogDoc implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** Индекс (аналог БД) */
    const ELS_INDEX = 'logdoc_index';
    /** Тип (аналог таблицы) */
    const ELS_TYPE = 'smslogdoc';

    /** @var bool Задавать xmlId в качестве id */
    protected $useXmlIdAsId = true;
    /** @var HandlerInterface */
    private static $handler;

    /**
     * @return Client
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function getClient(): Client
    {
        /** @var Client $elasticaClient */
        $elasticaClient = Application::getInstance()->getContainer()->get(
            'elastica.client'
        );

        return $elasticaClient;
    }

    /**
     * @return HandlerInterface
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getHandler()
    {
        if (!static::$handler) {
            static::$handler = new ElasticaAdapter(
                $this->getClient(),
                [
                    'index' => static::ELS_INDEX,
                    'type' => static::ELS_TYPE,
                ]
            );
        }

        return static::$handler;
    }

    /**
     * @return bool
     */
    public function isUseXmlAsId(): bool
    {
        return $this->useXmlIdAsId;
    }

    /**
     * @param bool $value
     * @return SmsLogDoc
     */
    public function setUseXmlAsId(bool $value): self
    {
        $this->useXmlIdAsId = $value;

        return $this;
    }

    /**
     * @param string $eventName
     * @param string $eventKey
     * @return string
     */
    protected function genXmlId(string $eventName, string $eventKey): string
    {
        return md5(serialize([trim($eventName), trim($eventKey)]));
    }

    /**
     * @param string $eventName
     * @param string $eventKey
     * @param string $value
     * @return ResultInterface|null
     */
    public function add(string $eventName, string $eventKey, string $value = ''): ?ResultInterface
    {
        $result = null;

        $tmpDocument = $this->useXmlIdAsId ? null : $this->get($eventName, $eventKey);
        if (!$tmpDocument) {
            $document = new Document();
            $document->setEntity($eventName);
            $document->setKey($eventKey);
            $document->setValue($value);
            $xmlId = $this->genXmlId($eventName, $eventKey);
            $document->setXmlId($xmlId);
            if ($this->useXmlIdAsId) {
                $document->setId($xmlId);
            }

            try {
                $result = $this->getHandler()->addDocument($document);
            } catch (\Exception $exception) {
                $this->log()->error(
                    $exception->getMessage(),
                    [
                        'eventName' => $eventName,
                        'eventKey' => $eventKey,
                        'value' => $value,
                        'xmlId' => $xmlId,
                    ]
                );
            }
        }

        return $result;
    }

    /**
     * @param string $eventName
     * @param string $eventKey
     * @return DocumentInterface|null
     */
    public function get(string $eventName, string $eventKey): ?DocumentInterface
    {
        $document = null;
        $xmlId = $this->genXmlId($eventName, $eventKey);
        try {
            if ($this->useXmlIdAsId) {
                $document = $this->getHandler()->getDocument($xmlId);
            } else {
                $documentCollection = $this->getHandler()->getDocumentsByXmlId($xmlId);
                if (!$documentCollection->isEmpty()) {
                    $document = $documentCollection->first();
                }
            }
        } catch (\Exception $exception) {
            $this->log()->error(
                $exception->getMessage(),
                [
                    'eventName' => $eventName,
                    'eventKey' => $eventKey,
                    'xmlId' => $xmlId,
                ]
            );
        }

        return $document;
    }

    /**
     * @param string|int $id
     * @return ResultInterface|null
     */
    public function delete($id): ?ResultInterface
    {
        $result = null;
        try {
            $result = $this->getHandler()->deleteDocument($id);
        } catch (\Exception $exception) {
            $this->log()->error(
                $exception->getMessage(),
                [
                    'id' => $id,
                ]
            );
        }

        return $result;
    }
}
