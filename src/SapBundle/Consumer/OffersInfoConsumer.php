<?php

namespace FourPaws\SapBundle\Consumer;

use FourPaws\SapBundle\Dto\In\Offers\Materials;

class OffersInfoConsumer implements ConsumerInterface
{
    /**
     * @param Materials $offersInfo
     *
     * @return bool
     */
    public function consume($offersInfo): bool
    {
        dump($offersInfo);
        die();
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool
    {
        return \is_object($data) && $data instanceof Materials;
    }
}
