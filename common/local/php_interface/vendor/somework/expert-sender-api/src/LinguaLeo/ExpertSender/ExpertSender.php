<?php

namespace LinguaLeo\ExpertSender;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use LinguaLeo\ExpertSender\Chunks\ChunkInterface;
use LinguaLeo\ExpertSender\Chunks\ColumnChunk;
use LinguaLeo\ExpertSender\Chunks\ColumnsChunk;
use LinguaLeo\ExpertSender\Chunks\DataChunk;
use LinguaLeo\ExpertSender\Chunks\GroupChunk;
use LinguaLeo\ExpertSender\Chunks\HeaderChunk;
use LinguaLeo\ExpertSender\Chunks\OrderByChunk;
use LinguaLeo\ExpertSender\Chunks\OrderByColumnsChunk;
use LinguaLeo\ExpertSender\Chunks\PrimaryKeyColumnsChunk;
use LinguaLeo\ExpertSender\Chunks\PropertiesChunk;
use LinguaLeo\ExpertSender\Chunks\PropertyChunk;
use LinguaLeo\ExpertSender\Chunks\ReceiverChunk;
use LinguaLeo\ExpertSender\Chunks\ReceiversChunk;
use LinguaLeo\ExpertSender\Chunks\SimpleChunk;
use LinguaLeo\ExpertSender\Chunks\SnippetChunk;
use LinguaLeo\ExpertSender\Chunks\SnippetsChunk;
use LinguaLeo\ExpertSender\Chunks\WhereChunk;
use LinguaLeo\ExpertSender\Chunks\WhereConditionsChunk;
use LinguaLeo\ExpertSender\Entities\Column;
use LinguaLeo\ExpertSender\Request\AddUserToList;
use LinguaLeo\ExpertSender\Results\ApiResult;
use LinguaLeo\ExpertSender\Results\ListResult;
use LinguaLeo\ExpertSender\Results\TableDataResult;
use LinguaLeo\ExpertSender\Results\UserIdResult;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class ExpertSender implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $apiKey;
    protected $endpointUrl;
    /**
     * @var \GuzzleHttp\ClientInterface|null
     */
    private $client;

    /**
     * @param string                           $endpointUrl - url without /Api
     * @param string                           $apiKey
     * @param \GuzzleHttp\ClientInterface|null $client
     * @param \Psr\Log\LoggerInterface         $logger
     */
    public function __construct($endpointUrl, $apiKey, ClientInterface $client, LoggerInterface $logger = null)
    {
        $endpointUrl = rtrim($endpointUrl, '/').'/';
        $this->endpointUrl = $endpointUrl.'Api/';

        $this->apiKey = $apiKey;
        $this->logger = $logger;
        $this->client = $client;
    }

    /**
     * Adds user to list subscribers.
     *
     * Calls with many arguments are deprecated. Pass Request\AddUserToList instead.
     *
     *
     * @param \LinguaLeo\ExpertSender\Request\AddUserToList $request
     *
     * @throws \BadMethodCallException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ApiResult
     */
    public function addUserToList(AddUserToList $request)
    {

        // we're going to use it, so we don't want it to be changeable anymore
        // (mutable object -> value object)
        // plus it gets validated for required fields
        $request->freeze();

        $headerChunk = $this->getAddUserToListHeaderChunk($request);
        $response = $this->client->request(
            'POST',
            $this->getUrl(ExpertSenderEnum::URL_SUBSCRIBERS),
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'text/xml',
                ],
                RequestOptions::BODY    => $headerChunk->getText(),
            ]
        );

        $apiResult = new ApiResult($response);
        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    public function getLists($seedLists = false)
    {
        $data = $this->getBaseData();
        if ($seedLists) {
            $data['seedLists'] = 'true';
        }

        $response = $this->client->request(
            'GET',
            $this->getUrl(ExpertSenderEnum::URL_GET_LISTS),
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'text/xml',
                ],
                RequestOptions::QUERY   => $data,
            ]
        );

        $apiResult = new ListResult($response);
        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    /**
     * @param     $email
     * @param int $listId
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ApiResult
     */
    public function deleteUser($email, $listId = null)
    {
        $data = $this->getBaseData();
        $data['email'] = $email;
        if ($listId !== null) {
            $data['listId'] = $listId;
        }

        $response = $this->client->request(
            'DELETE',
            $this->getUrl(ExpertSenderEnum::URL_SUBSCRIBERS),
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'text/xml',
                ],
                RequestOptions::QUERY   => $data,
            ]
        );

        $apiResult = new ApiResult($response);
        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    /**
     * @param $email
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return UserIdResult
     */
    public function getUserId($email)
    {
        $data = $this->getBaseData();
        $data['email'] = $email;
        $data['option'] = '3';

        $response = $this->client->request(
            'GET',
            $this->getUrl(ExpertSenderEnum::URL_SUBSCRIBERS),
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'text/xml',
                ],
                RequestOptions::QUERY   => $data,
            ]
        );

        $apiResult = new UserIdResult($response);
        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    /**
     * @param string   $tableName
     * @param Column[] $columns
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ApiResult
     */
    public function addTableRow($tableName, array $columns)
    {
        $tableNameChunk = new SimpleChunk('TableName', $tableName);
        $dataChunk = new DataChunk();
        $columnsChunks = [];
        foreach ($columns as $column) {
            $columnsChunks[] = new ColumnChunk($column);
        }
        $columnsChunk = new ColumnsChunk($columnsChunks);
        $dataChunk->addChunk($columnsChunk);
        $groupChunk = new GroupChunk([$tableNameChunk, $dataChunk]);
        $headerChunk = $this->getHeaderChunk($groupChunk);

        $response = $this->client->request(
            'POST',
            $this->getUrl(ExpertSenderEnum::URL_ADD_TABLE_ROW),
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'text/xml',
                ],
                RequestOptions::BODY    => $headerChunk->getText(),
            ]
        );

        $apiResult = new ApiResult($response);
        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    /**
     * @param       $tableName
     * @param array $columns
     * @param array $where
     * @param array $orderBy
     * @param mixed $limit
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return \LinguaLeo\ExpertSender\Results\TableDataResult
     */
    public function getTableData(
        $tableName,
        array $columns = [],
        array $where = [],
        array $orderBy = [],
        $limit = null
    ) {
        $tableNameChunk = new SimpleChunk('TableName', $tableName);
        $columnsChunks = $whereChunks = $orderByChunks = [];
        foreach ($columns as $column) {
            $columnsChunks[] = new ColumnChunk($column);
        }
        foreach ($where as $condition) {
            $whereChunks[] = new WhereChunk($condition);
        }
        foreach ($orderBy as $direction) {
            $orderByChunks[] = new OrderByChunk($direction);
        }
        $groupChunk = new GroupChunk([$tableNameChunk]);
        if ($columnsChunks) {
            $groupChunk->addChunk(new ColumnsChunk($columnsChunks));
        }
        if ($whereChunks) {
            $groupChunk->addChunk(new WhereConditionsChunk($whereChunks));
        }
        if ($orderByChunks) {
            $groupChunk->addChunk(new OrderByColumnsChunk($orderByChunks));
        }
        if ($limit) {
            $limitChunk = new SimpleChunk('Limit', (int) $limit);
            $groupChunk->addChunk($limitChunk);
        }
        $headerChunk = $this->getHeaderChunk($groupChunk);

        $response = $this->client->request(
            'POST',
            $this->getUrl(ExpertSenderEnum::URL_GET_TABLE_DATA),
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'text/xml',
                ],
                RequestOptions::BODY    => $headerChunk->getText(),
            ]
        );

        $apiResult = new TableDataResult($response);
        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    /**
     * @param string $tableName
     * @param array  $primaryKeyColumns
     * @param array  $columns
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ApiResult
     */
    public function updateTableRow($tableName, array $primaryKeyColumns, array $columns)
    {
        $tableNameChunk = new SimpleChunk('TableName', $tableName);
        $primaryKeysColumnsChunks = $columnsChunks = [];
        foreach ($primaryKeyColumns as $column) {
            $primaryKeysColumnsChunks[] = new ColumnChunk($column);
        }
        foreach ($columns as $column) {
            $columnsChunks[] = new ColumnChunk($column);
        }
        $primaryKeyColumnsChunk = new PrimaryKeyColumnsChunk($primaryKeysColumnsChunks);
        $columnsChunk = new ColumnsChunk($columnsChunks);
        $groupChunk = new GroupChunk([$tableNameChunk, $primaryKeyColumnsChunk, $columnsChunk]);
        $headerChunk = $this->getHeaderChunk($groupChunk);

        $response = $this->client->request(
            'POST',
            $this->getUrl(ExpertSenderEnum::URL_UPDATE_TABLE_ROW),
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'text/xml',
                ],
                RequestOptions::BODY    => $headerChunk->getText(),
            ]
        );

        $apiResult = new ApiResult($response);
        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    /**
     * @param string   $tableName
     * @param Column[] $primaryKeyColumns
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ApiResult
     */
    public function deleteTableRow($tableName, array $primaryKeyColumns)
    {
        $tableNameChunk = new SimpleChunk('TableName', $tableName);
        $primaryKeysColumnsChunks = [];
        foreach ($primaryKeyColumns as $column) {
            $primaryKeysColumnsChunks[] = new ColumnChunk($column);
        }
        $primaryKeyColumnsChunk = new PrimaryKeyColumnsChunk($primaryKeysColumnsChunks);
        $groupChunk = new GroupChunk([$tableNameChunk, $primaryKeyColumnsChunk]);
        $headerChunk = $this->getHeaderChunk($groupChunk);

        $response = $this->client->request(
            'POST',
            $this->getUrl(ExpertSenderEnum::URL_DELETE_TABLE_ROW),
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'text/xml',
                ],
                RequestOptions::BODY    => $headerChunk->getText(),
            ]
        );

        $apiResult = new ApiResult($response);
        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    /**
     * @param $listId
     * @param $from
     * @param $to
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ApiResult
     */
    public function changeEmail($listId, $from, $to)
    {
        $result = $this->getUserId($from);

        $request = (new Request\AddUserToList())
            ->setMode(ExpertSenderEnum::MODE_ADD_AND_UPDATE)
            ->setId($result->getId())
            ->setListId($listId)
            ->setEmail($to)
            ->freeze();

        $apiResult = $this->addUserToList($request);

        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    /**
     * @param int   $triggerId
     * @param array $receivers
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ApiResult
     */
    public function sendTrigger($triggerId, array $receivers)
    {
        $receiverChunks = [];
        foreach ($receivers as $receiver) {
            $receiverChunks[] = new ReceiverChunk($receiver);
        }

        $receiversChunks = new ReceiversChunk($receiverChunks);
        $dataChunk = new DataChunk('TriggerReceivers');
        $dataChunk->addChunk($receiversChunks);
        $headerChunk = $this->getHeaderChunk($dataChunk);

        $response = $this->client->request(
            'POST',
            $this->getUrl(ExpertSenderEnum::URL_TRIGGER_PATTERN, $triggerId),
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'text/xml',
                ],
                RequestOptions::BODY    => $headerChunk->getText(),
            ]
        );

        $apiResult = new ApiResult($response);
        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    /**
     * @param       $transactionId
     * @param       $receiver
     * @param array $snippets
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ApiResult
     */
    public function sendTransactional($transactionId, $receiver, array $snippets = [])
    {
        $snippetChunks = [];
        foreach ($snippets as $snippet) {
            $snippetChunks[] = new SnippetChunk($snippet);
        }

        $receiverChunk = new ReceiverChunk($receiver);
        $snippetsChunks = new SnippetsChunk($snippetChunks);
        $dataChunk = new DataChunk();
        $dataChunk->addChunk($receiverChunk);
        $dataChunk->addChunk($snippetsChunks);
        $headerChunk = $this->getHeaderChunk($dataChunk);

        $response = $this->client->request(
            'POST',
            $this->getUrl(ExpertSenderEnum::URL_TRANSACTIONAL_PATTERN, $transactionId),
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'text/xml',
                ],
                RequestOptions::BODY    => $headerChunk->getText(),
            ]
        );

        $apiResult = new ApiResult($response);
        $this->logApiResult(__METHOD__, $apiResult);

        return $apiResult;
    }

    protected function getUrl(...$parameters)
    {
        return $this->endpointUrl.sprintf(...$parameters);
    }

    /**
     * @param AddUserToList $request
     *
     * @return HeaderChunk
     */
    protected function getAddUserToListHeaderChunk(Request\AddUserToList $request)
    {
        $dataChunk = new DataChunk('Subscriber');

        $dataChunk->addChunk(new SimpleChunk('Mode', $request->getMode()));
        $dataChunk->addChunk(new SimpleChunk('Email', $request->getEmail()));
        $dataChunk->addChunk(new SimpleChunk('ListId', $request->getListId()));

        if ($request->getFirstName() !== null) {
            $dataChunk->addChunk(new SimpleChunk('Firstname', $request->getFirstName()));
        }

        if ($request->getLastName() !== null) {
            $dataChunk->addChunk(new SimpleChunk('Lastname', $request->getLastName()));
        }

        if ($request->getName() !== null) {
            $dataChunk->addChunk(new SimpleChunk('Name', $request->getName()));
        }

        if ($request->getId() !== null) {
            $dataChunk->addChunk(new SimpleChunk('Id', $request->getId()));
        }

        if ($request->getTrackingCode() !== null) {
            $dataChunk->addChunk(new SimpleChunk('TrackingCode', $request->getTrackingCode()));
        }

        if ($request->getVendor() !== null) {
            $dataChunk->addChunk(new SimpleChunk('Vendor', $request->getVendor()));
        }

        if ($request->getIp() !== null) {
            $dataChunk->addChunk(new SimpleChunk('Ip', $request->getIp()));
        }

        if ($request->getPhone() !== null) {
            $dataChunk->addChunk(new SimpleChunk('Phone', $request->getPhone()));
        }

        if ($request->getCustomSubscriberId() !== null) {
            $dataChunk->addChunk(new SimpleChunk('CustomSubscriberId', $request->getCustomSubscriberId()));
        }

        $dataChunk->addChunk(new SimpleChunk('Force', $request->getForce() ? 'true' : 'false'));

        $propertiesChunks = new PropertiesChunk();

        foreach ($request->getProperties() as $property) {
            $propertiesChunks->addChunk(new PropertyChunk($property));
        }

        $dataChunk->addChunk($propertiesChunks);

        return $this->getHeaderChunk($dataChunk);
    }

    /**
     * @param ChunkInterface $bodyChunk
     *
     * @return HeaderChunk
     */
    protected function getHeaderChunk(ChunkInterface $bodyChunk)
    {
        return new HeaderChunk($this->apiKey, $bodyChunk);
    }

    /**
     * @return array
     */
    protected function getBaseData()
    {
        return ['apiKey' => $this->apiKey];
    }

    /**
     * @param string    $method
     * @param ApiResult $result
     */
    protected function logApiResult($method, ApiResult $result)
    {
        if ($this->logger === null) {
            return;
        }

        $level = $result->isOk() ? LogLevel::INFO : LogLevel::ERROR;
        $this->logger->log($level, sprintf('ES method "%s"', $method), (array) $result);
    }
}
