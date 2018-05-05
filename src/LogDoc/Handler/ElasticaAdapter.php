<?php

namespace FourPaws\LogDoc\Handler;

use Elastica\Client;
use Elastica\Query;
use Elastica\Query\Term;
use FourPaws\LogDoc\Collection\DocumentCollection;
use FourPaws\LogDoc\Model\AddDocumentResult;
use FourPaws\LogDoc\Model\DeleteDocumentResult;
use FourPaws\LogDoc\Model\Document;
use FourPaws\LogDoc\Model\DocumentInterface;
use FourPaws\LogDoc\Model\ResultInterface;
use FourPaws\LogDoc\Model\UpdateDocumentResult;

class ElasticaAdapter implements HandlerInterface
{
    /** @var Client */
    protected $client;
    /** @var array */
    protected $options = [];

    public function __construct(Client $client, $options = [])
    {
        $this->client = $client;

        $this->options = array_merge(
            [
                'index' => 'logdoc_index',
                'type' => 'logdoc_type',
            ],
            $options
        );
    }

    /**
     * @return Client
     */
    protected function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return array
     */
    protected function getBaseParams(): array
    {
        $params = [
            'index' => $this->options['index'],
            'type' => $this->options['type'],
        ];

        return $params;
    }

    /**
     * @param array $params
     * @return \Elastica\Document
     */
    protected function convertToElasticaDocument(array $params): \Elastica\Document
    {
        $elasticaDocument = new \Elastica\Document(
            $params['id'] ?? '',
            $params['body'] ?? [],
            $params['type'] ?? '',
            $params['index'] ?? ''
        );

        return $elasticaDocument;
    }

    /**
     * @param \Elastica\Document $document
     * @return array
     */
    protected function convertToArray(\Elastica\Document $document): array
    {
        $params = [
            'id' => $document->getId(),
            'body' => $document->getData(),
            'type' => $document->getType(),
            'index' => $document->getIndex(),
        ];

        return $params;
    }

    /**
     * @param array $elasticaParams
     * @return Document
     */
    protected function convertToDocument(array $elasticaParams): Document
    {
        $document = new Document();
        $document->setId($elasticaParams['id'] ?? '');
        $document->setValue($elasticaParams['body']['value'] ?? '');
        $document->setEntity($elasticaParams['body']['entity'] ?? '');
        $document->setKey($elasticaParams['body']['key'] ?? '');
        $document->setXmlId($elasticaParams['body']['xmlId'] ?? '');

        return $document;
    }

    /**
     * @param DocumentInterface $document
     * @return array
     */
    protected function obtainElasticaParams(DocumentInterface $document): array
    {
        $params = $this->getBaseParams();
        $params['id'] = $document->getId();
        $params['body'] = [
            'entity' => $document->getEntity(),
            'key' => $document->getKey(),
            'xmlId' => $document->getXmlId(),
            'value' => $document->getValue(),
        ];

        return $params;
    }

    /**
     * @param DocumentInterface $document
     * @return ResultInterface
     */
    public function addDocument(DocumentInterface $document): ResultInterface
    {
        $elasticaRes = $this->getClient()->addDocuments(
            [
                $this->convertToElasticaDocument(
                    $this->obtainElasticaParams($document)
                )
            ]
        );

        $result = new AddDocumentResult();
        $result->setResult($elasticaRes);
        if ($elasticaRes->isOk()) {
            $result->setSuccess(true);
        }

        return $result;
    }

    /**
     * @param DocumentInterface $document
     * @return ResultInterface
     */
    public function updateDocument(DocumentInterface $document): ResultInterface
    {
        $res = $this->getClient()->updateDocuments(
            [
                $this->convertToElasticaDocument(
                    $this->obtainElasticaParams($document)
                )
            ]
        );

        $result = new UpdateDocumentResult();
        $result->setResult($res);
        if ($res->isOk()) {
            $result->setSuccess(true);
        }

        return $result;
    }

    /**
     * @param string|int $id
     * @return DocumentInterface
     */
    public function getDocument($id): DocumentInterface
    {
        $params = $this->getBaseParams();
        $params['id'] = $id;

        $elasticaDocument = $this->getClient()
            ->getIndex($params['index'])
            ->getType($params['type'])
            ->getDocument($params['id']);

        $document = $this->convertToDocument(
            $this->convertToArray($elasticaDocument)
        );

        return $document;
    }

    /**
     * @param string $xmlId
     * @return DocumentCollection
     */
    public function getDocumentsByXmlId(string $xmlId): DocumentCollection
    {
        $textQuery = new Term(
            [
                'xmlId' => $xmlId,
            ]
        );
        $query = Query::create($textQuery);

        $params = $this->getBaseParams();
        $searchRes = $this->getClient()
            ->getIndex($params['index'])
            ->getType($params['type'])
            ->search($query);

        $result = new DocumentCollection();
        /** @var \Elastica\Document $elasticaDocument */
        foreach ($searchRes->getDocuments() as $elasticaDocument) {
            $result->add(
                $this->convertToDocument(
                    $this->convertToArray($elasticaDocument)
                )
            );
        }

        return $result;
    }

    /**
     * @param $id
     * @return ResultInterface
     */
    public function deleteDocument($id): ResultInterface
    {
        $params = $this->getBaseParams();
        $params['id'] = $id;

        $res = $this->getClient()
            ->getIndex($params['index'])
            ->getType($params['type'])
            ->deleteById($params['id']);

        $result = new DeleteDocumentResult();
        $result->setResult($res);
        if ($res->isOk()) {
            $result->setSuccess(true);
        }

        return $result;
    }
}
