<?php

namespace FourPaws\SaleBundle\Repository;

use FourPaws\BitrixOrmBundle\Orm\D7Repository;
use FourPaws\SaleBundle\Entity\ForgotBasket;
use FourPaws\SaleBundle\Exception\ForgotBasket\NotFoundException;

class ForgotBasketRepository extends D7Repository
{
    /**
     * @param int $fuserId
     *
     * @return ForgotBasket
     * @throws NotFoundException
     */
    public function findByFuserId($fuserId): ForgotBasket
    {
        $result = parent::findBy(['UF_FUSER_ID' => $fuserId])->first();
        if (!$result instanceof ForgotBasket) {
            throw new NotFoundException(\sprintf('Task for fuser #%s not found', $fuserId));
        }

        return $result;
    }
}
