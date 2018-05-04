<?php

namespace FourPaws\LogDoc\Handler;

use FourPaws\LogDoc\Collection\DocumentCollection;
use FourPaws\LogDoc\Model\DocumentInterface;
use FourPaws\LogDoc\Model\ResultInterface;

interface HandlerInterface
{
    /**
     * @param DocumentInterface $document
     * @return ResultInterface
     */
    public function addDocument(DocumentInterface $document): ResultInterface;

    /**
     * @param $id
     * @return DocumentInterface
     */
    public function getDocument($id): DocumentInterface;

    /**
     * @param string $xmlId
     * @return DocumentCollection
     */
    public function getDocumentsByXmlId(string $xmlId): DocumentCollection;

    /**
     * @param DocumentInterface $document
     * @return ResultInterface
     */
    public function updateDocument(DocumentInterface $document): ResultInterface;

    /**
     * @param $id
     * @return ResultInterface
     */
    public function deleteDocument($id): ResultInterface;
}
