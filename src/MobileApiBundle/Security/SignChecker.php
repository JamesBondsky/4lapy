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
        switch ($request->getMethod()) {
            case Request::METHOD_GET:
            case Request::METHOD_DELETE:
                $paramBag = $request->request;
                break;
            default:
                $paramBag = $request->query;
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

        return $sign === md5($this->salt . implode('', $arMd5));
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
