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
        
        throw new \Exception('Timer is empty');
    }
    
    /**
     * @param string $format
     *
     * @return string
     */
    public function getFormattedTime(string $format = 'h\h i\m s\s')
    {
        return (new \DateTime())->diff(new \DateTime())->format($format);
    }
}