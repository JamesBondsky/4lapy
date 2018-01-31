<?php

namespace FourPaws\MobileApiBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class SignListener implements ListenerInterface
{
    const DEFAULT_SALT = 'ABCDEF00G';
    const SIGN_FIELD = 'sign';

    private $salt = SignListener::DEFAULT_SALT;

    public function setSalt(string $salt)
    {
        $this->salt = $salt;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();


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

        if ($this->checkSign($sign, $params)) {
            return;
        }

        $response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
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

    protected function checkSign(string $sign, array $params = [])
    {
        $arMd5 = $this->md5ValueRecursive($params);
        sort($arMd5);

        return $sign === md5($this->salt . implode('', $arMd5));
    }
}
