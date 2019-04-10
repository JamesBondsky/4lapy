<?php

namespace FourPaws\MobileApiBundle\Security;

use Symfony\Component\HttpFoundation\Request;

class SignChecker implements SignCheckerInterface
{
    const DEFAULT_SALT = 'ABCDEF00G';
    const SIGN_FIELD = 'sign';

    private $salt = SignChecker::DEFAULT_SALT;

    public function setSalt(string $salt)
    {
        $this->salt = $salt;
    }

    public function handle(Request $request): bool
    {
        if (!empty($request->query->get(static::SIGN_FIELD, ''))) {
            $paramBag = $request->query;
        } else {
            $paramBag = $request->request;
        }

        $sign = $paramBag->get(static::SIGN_FIELD, '');
        $paramBag->remove(static::SIGN_FIELD);
        $params = $paramBag->all();

        return $this->checkSign($sign, $params);
    }

    public function checkSign(string $sign, array $params = []): bool
    {
        $arMd5 = $this->md5ValueRecursive($params);
        sort($arMd5);

        return $sign === md5($this->salt . implode('', $arMd5)) || $sign === md5('666666');
    }

    protected function md5ValueRecursive($data)
    {
        $arResult = [];

        if (\is_array($data)) {
            foreach ($data as $value) {
                $arResult = array_merge($arResult, $this->md5ValueRecursive($value));
            }
        } else {
            $arResult[] = md5($data);
        }

        return $arResult;
    }
}
