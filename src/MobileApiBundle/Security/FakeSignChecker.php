<?php

namespace FourPaws\MobileApiBundle\Security;

use Symfony\Component\HttpFoundation\Request;

class FakeSignChecker implements SignCheckerInterface
{

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function handle(Request $request): bool
    {
        return true;
    }

    /**
     * @param string $sign
     * @param array  $params
     *
     * @return bool
     */
    public function checkSign(string $sign, array $params = []): bool
    {
        return true;
    }
}
