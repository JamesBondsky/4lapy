<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Fixtures;

use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\ObjectSet;

class FixtureService
{
    const PATH_YML = __DIR__ . '/../../Resources/fixtures/';

    /**
     * @var ObjectSet
     */
    protected $objectSet;

    protected $loaded = false;

    /**
     * @return ObjectSet
     */
    public function getCommon()
    {
        $this->loadCommon();
        return $this->objectSet;
    }

    /**
     * @param     $class
     * @param int $sliceSize
     *
     * @throws \RuntimeException
     * @return array
     */
    public function get($class, int $sliceSize = 1)
    {
        $this->loadCommon();
        $result = array_filter($this->objectSet->getObjects(), function ($data) use ($class) {
            return $class && $data instanceof $class;
        });
        if (!$result) {
            throw new \RuntimeException(sprintf('No such fixtures for %s', $class));
        }
        return \array_slice(\array_values($result), 0, $sliceSize);
    }

    protected function loadCommon()
    {
        if (!$this->loaded) {
            $loader = new NativeLoader();
            $this->objectSet = $loader->loadFile(static::PATH_YML . 'common.yml');
            $this->loaded = true;
        }
    }
}
