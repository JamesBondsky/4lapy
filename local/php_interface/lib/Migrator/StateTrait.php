<?php

namespace FourPaws\Migrator;

trait StateTrait
{
    private $add    = 0;
    
    private $update = 0;
    
    private $error  = 0;
    
    private $timer  = 0;
    
    /**
     * @return int
     */
    public function getAddCount() : int
    {
        return $this->add;
    }
    
    /**
     * Increase add count
     */
    public function incAdd()
    {
        $this->add++;
    }
    
    /**
     * @return int
     */
    public function getUpdateCount() : int
    {
        return $this->update;
    }
    
    /**
     * Increase update count
     */
    public function incUpdate()
    {
        $this->update++;
    }
    
    /**
     * @return int
     */
    public function getErrorCount() : int
    {
        return $this->error;
    }
    
    /**
     * Increase error count
     */
    public function incError()
    {
        $this->error++;
    }
    
    /**
     * @return int
     */
    public function getFullCount() : int
    {
        return $this->add + $this->error + $this->update;
    }
    
    /**
     * Start timer
     */
    public function startTimer()
    {
        $this->timer = time();
    }
    
    /**
     * @return int
     *
     * @throws \Exception
     */
    public function getTime() : int
    {
        if ($this->timer) {
            return time() - $this->timer;
        }
    
        /**
         * @todo Впилить нормальный Exception
         */
        throw new \Exception('Timer is empty');
    }
    
    /**
     * @param string $format
     *
     * @return string
     * @throws \Exception
     */
    public function getFormattedTime(string $format = '%hh %im %ss') : string
    {
        if ($this->timer) {
            return \DateTime::createFromFormat('U', time())
                            ->diff(\DateTime::createFromFormat('U', $this->timer))
                            ->format($format);
        }
        
        /**
         * @todo Впилить нормальный Exception
         */
        throw new \Exception('Timer is empty');
    }
}