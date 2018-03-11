<?php

namespace FourPaws\MobileApiBundle\Security;

use Symfony\Component\HttpFoundation\Request;

interface SignCheckerInterface
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function handle(Request $request): bool;

    /**
     * @param string $sign
     * @param array  $params
     *
     * @return bool
     */
    public function checkSign(string $sign, array $params = []): bool;
}
