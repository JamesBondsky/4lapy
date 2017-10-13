<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\User;

class UserCollection extends CdbResultCollectionBase
{
    /**
     * @return \Generator User[]
     */
    protected function fetchElement() : \Generator
    {
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new User($fields);
        }
    }
}