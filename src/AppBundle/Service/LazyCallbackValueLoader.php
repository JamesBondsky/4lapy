<?php

namespace FourPaws\AppBundle\Service;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;

class LazyCallbackValueLoader
{
    public function load(string $objectClass, callable $callable)
    {
        return (new LazyLoadingValueHolderFactory())->createProxy(
            $objectClass,
            function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) use ($callable) {
                $wrappedObject = $callable();
                $initializer = null; // turning off further lazy initialization
            }
        );
    }
}
