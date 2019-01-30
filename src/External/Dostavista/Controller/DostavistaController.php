<?php

namespace FourPaws\External\Dostavista\Controller;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Entity\BaseEntity;
use Symfony\Component\HttpFoundation\Request;

class DostavistaController
{

    public function __construct()
    {
    }

    public function deliveryDostavistaOrderChangeAction(Request $request): JsonResponse
    {
        $queryStr = $request->getQueryString();
        $data = $request->request->all();

        $testMode = (\COption::GetOptionString('articul.dostavista.delivery', 'dev_mode', '') == BaseEntity::BITRIX_TRUE);
        if ($testMode) {
            $token = \COption::GetOptionString('articul.dostavista.delivery', 'token_dev', '');
        } else {
            $token = \COption::GetOptionString('articul.dostavista.delivery', 'token_prod', '');
        }

        if ($data['event'] == 'order_changed') {
            $validationErrors[] = 'event is not order_changed';
        }

        if (empty($validationErrors) && MD5($token . $queryStr) == $data['signature']) {

        }

        if (!empty($validationErrors)) {
            return JsonErrorResponse::createWithData(
                'errors!',
                ['error' => $validationErrors],
                200,
                []
            );
        }

        return JsonSuccessResponse::create(
            'success!',
            200,
            []
        );
    }
}