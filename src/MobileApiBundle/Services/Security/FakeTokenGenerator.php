<?php

namespace FourPaws\MobileApiBundle\Services\Security;

class FakeTokenGenerator implements TokenGeneratorInterface
{
    const FAKE_TOKEN = '666666';

    /**
     * @return string
     */
    public function generate(): string
    {
        return md5(static::FAKE_TOKEN);
    }
}
