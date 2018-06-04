<?php

namespace FourPaws\External\SmsTraffic\Sms;

use DateTime;

/**
 * Class AbstractSms
 */
abstract class AbstractSms implements ConstInterface
{
    /**
     * Parameters of sms sending
     *
     * @var array
     */
    protected $parameters = [];
    
    /**
     * AbstractSms constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = [
            self::PARAMETER_ENCODING => self::ENCODING_UTF8,
        ];
        $this->updateParameters($parameters);
    }
    
    /**
     * Setter for sms parameter
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
        
        return $this;
    }
    
    /**
     * Getter for sms parameter
     *
     * @param string $name
     *
     * @return string
     */
    public function getParameter($name) : string
    {
        return $this->parameters[$name];
    }
    
    /**
     * Returns sms sending parameters
     *
     * @return array
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
    
    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;
        
        return $this;
    }
    
    /**
     * Parameters batch updating
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function updateParameters(array $parameters): self
    {
        $this->parameters = array_merge($this->parameters, $parameters);
        
        return $this;
    }
    
    /**
     * Set encoding
     *
     * @param int $encoding
     *
     * @return $this
     */
    public function setEncoding($encoding): self
    {
        $this->setParameter(self::PARAMETER_ENCODING, $encoding);
        
        return $this;
    }
    
    /**
     * Set date, when sms will be sent
     *
     * @param DateTime $date
     *
     * @return $this
     */
    public function setStartDate(DateTime $date): self
    {
        $this->setParameter(self::PARAMETER_START_DATE, $date->format(self::DATE_FORMAT));
        
        return $this;
    }
    
    /**
     * Set delay between sms sending
     *
     * @param float $gap
     *
     * @return $this
     */
    public function setGap($gap): self
    {
        $this->setParameter(self::PARAMETER_GAP, $gap);
        
        return $this;
    }
}
