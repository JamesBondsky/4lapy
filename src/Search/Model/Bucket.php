<?php

namespace FourPaws\Search\Model;

use JMS\Serializer\Annotation\Type;

class Bucket
{
    /**
     * @var string
     * @Type("string")
     */
    protected $key = '';

    /**
     * @var int
     * @Type("int")
     */
    protected $doc_count = 0;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function withKey(string $key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return int
     */
    public function getDocCount(): int
    {
        return $this->doc_count;
    }

    /**
     * @param int $doc_count
     *
     * @return $this
     */
    public function withDocCount(int $doc_count)
    {
        $this->doc_count = $doc_count;

        return $this;
    }

}
