<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\UserTable;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Request\CardActivatedRequest;
use FourPaws\MobileApiBundle\Dto\Response;

class CardService
{
    /**
     * @param CardActivatedRequest $request
     *
     * @return Response
     */
    public function isActive(CardActivatedRequest $request): Response
    {
        $activated = UserTable::query()
                ->addFilter('UF_DISCOUNT_CARD', $request->getNumber())
                ->exec()
                ->getSelectedRowsCount() > 0;
        $cardResponse = new Response\CardActivatedResponse(
            $activated,
            $activated ? 'Карта уже привязана к другому аккаунту. Пожалуйста, используйте другую карту' : ''
        );

        $apiResponse = new Response($cardResponse);
        if ($activated) {
            $apiResponse->addError(new Error(42, 'Данная карта уже привязана'));
        }
        return $apiResponse;
    }
}
