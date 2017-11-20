<?php

namespace FourPaws\External\SmsTraffic\Exception;

/**
 * Class SendingException
 */
class SendingException extends SmsTrafficApiException
{
    /**
     * @var string
     */
    protected $answer;
    
    /**
     * @return string
     */
    public function getAnswer() : string
    {
        return $this->answer;
    }
    
    /**
     * @param string $answer
     *
     * @return $this
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
        
        return $this;
    }
}
