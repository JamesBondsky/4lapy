<?php

namespace LinguaLeo\ExpertSender\Results;

use LinguaLeo\ExpertSender\Entities\EsList;
use LinguaLeo\ExpertSender\ExpertSenderException;
use Psr\Http\Message\ResponseInterface;

class ListResult extends ApiResult
{
    /**
     * @var array|EsList[]
     */
    private $lists;

    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);
        $this->lists = [];
        $this->parse();
    }

    /**
     * @return array|EsList[]
     */
    public function getLists()
    {
        return $this->lists;
    }

    protected function parse()
    {
        if (!$this->isOk()) {
            throw new ExpertSenderException("Can't get lists");
        }

        $body = $this->response->getBody()->__toString();
        $xml = new \SimpleXMLElement($body);
        $xmlLists = $xml->xpath('/ApiResponse/Data/Lists/List');
        foreach ($xmlLists as $xmlList) {
            $this->lists[] = new EsList(
                (int) $xmlList->xpath('Id')[0],
                (string) $xmlList->xpath('Name')[0],
                $xmlList->xpath('FriendlyName') ? (string) $xmlList->xpath('FriendlyName')[0] : '',
                (string) $xmlList->xpath('Language')[0],
                (string) $xmlList->xpath('OptInMode')[0]
            );
        }
    }
}
