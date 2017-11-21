<?php

namespace LinguaLeo\ExpertSender\Results;

use Psr\Http\Message\ResponseInterface;

class TableDataResult extends ApiResult
{
    /** @var array */
    protected $data = [];

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);
        $this->parse();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    protected function parse()
    {
        if ($this->isOk()) {
            $response = $this->removeBOM($this->response->getBody()->__toString());
            $temp = tmpfile();
            fwrite($temp, $response);
            fseek($temp, 0);
            while (($row = fgetcsv($temp)) !== false) {
                $this->data[] = $row;
            }
            fclose($temp);
        }
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function removeBOM($text)
    {
        $bom = pack('H*', 'EFBBBF');

        return preg_replace("/^{$bom}/", '', $text);
    }
}
