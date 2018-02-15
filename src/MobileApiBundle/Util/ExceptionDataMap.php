<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Util;

class ExceptionDataMap
{
    /**
     * @var array
     */
    private $map;

    public function __construct(array $map = [])
    {
        $map = array_filter($map, function ($configs) {
            return
                $configs &&
                \is_array($configs);
        });
        $this->map = $map;
    }

    /**
     * @param \Exception $exception
     *
     * @return string
     */
    public function resolveCode(\Exception $exception): string
    {
        return $this->resolveException($exception)['code'] ?? '';
    }

    /**
     * @param \Exception $exception
     *
     * @return string
     */
    public function resolveMessage(\Exception $exception): string
    {
        return $this->resolveException($exception)['message'] ?? '';
    }

    /**
     * @param \Exception $exception
     *
     * @return string
     */
    public function resolveStatusCode(\Exception $exception): string
    {
        return $this->resolveException($exception)['status_code'] ?? '';
    }

    /**
     * @param \Exception $exception
     *
     * @return array
     */
    protected function resolveException(\Exception $exception): array
    {
        return $this->resolveConfig(get_class($exception));
    }

    /**
     * @param $class
     *
     * @return array
     */
    protected function resolveConfig(string $class): array
    {
        foreach ($this->map as $mapClass => $configs) {
            if ($class === $mapClass) {
                return $configs;
            }
        }

        foreach ($this->map as $mapClass => $configs) {
            if (is_subclass_of($class, $mapClass)) {
                return $configs;
            }
        }
        return [];
    }
}
