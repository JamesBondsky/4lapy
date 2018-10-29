<?php

namespace FourPaws\Search\Model;

use Elastica\Result;

class HitMetaInfo
{
    /**
     * @var int
     */
    private $score = 0;

    /**
     * @var array
     */
    private $matchedQueries = [];

    /**
     * @var array
     */
    private $highlight = [];

    /**
     * @param Result $result
     *
     * @return HitMetaInfo
     */
    public static function create(Result $result): HitMetaInfo
    {
        $self = new self();

        $hit = $result->getHit();
        if (isset($hit['matched_queries']) && \is_array($hit['matched_queries'])) {
            $self->withMatchedQueries($hit['matched_queries']);
        }
        if(isset($hit['highlight'])) {
            $self->withHighlight($hit['highlight']);
        }

        $score = $result->getScore();
        if (is_numeric($score)) {
            $self->withScore($score);
        }

        return $self;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @param int $score
     *
     * @return $this
     */
    public function withScore(int $score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return array
     */
    public function getMatchedQueries(): array
    {
        return $this->matchedQueries;
    }

    /**
     * @param array $matchedQueries
     *
     * @return $this
     */
    public function withMatchedQueries(array $matchedQueries)
    {
        $this->matchedQueries = $matchedQueries;

        return $this;
    }

    /**
     * @param array $highlight
     */
    public function withHighlight(array $highlight)
    {
        $this->highlight = $highlight;
    }

    /**
     * @return array
     */
    public function getHighlight(): array
    {
        return $this->highlight;
    }

}
