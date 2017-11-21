<?php

namespace LinguaLeo\ExpertSender\Results;

use LinguaLeo\ExpertSender\ExpertSenderException;
use Psr\Http\Message\ResponseInterface;

class UserIdResult extends ApiResult
{
    protected $id;

    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        $this->parseBody();
    }

    public function parseBody()
    {
        if (!$this->isOk()) {
            throw new ExpertSenderException("Can't get user id");
        }
        $body = $this->response->getBody()->__toString();
        $xml = new \SimpleXMLElement($body);

        $idXml = $xml->xpath('/ApiResponse/Data/Id');
        if (!is_array($idXml) || count($idXml) === 0) {
            throw new ExpertSenderException("Can't get user id");
        }

        $this->id = (string) $idXml[0];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
