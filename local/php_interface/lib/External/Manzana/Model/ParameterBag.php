<?php

namespace FourPaws\External\Manzana\Model;

final class ParameterBag
{
    /**
     * @var Parameter[] $parameters
     */
    private $parameters;
    
    public function __construct(array $parameters)
    {
        foreach ($parameters as $k => $v) {
            $this->parameters[] = $v instanceof Parameter ? $v : new Parameter($k, $v);
        }
    }
    
    /**
     * @return array
     */
    public function getParameters() : array
    {
        $result = [];
        
        foreach ($this->parameters as $parameter) {
            $result[] = $parameter->getParameter();
        }
        
        return $result;
    }
}
