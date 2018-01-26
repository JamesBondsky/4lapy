<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\IblockSect;
use Generator;

class IblockSectCollection extends CdbResultCollectionBase
{
    /**
     * @inheritdoc
     */
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new IblockSect($fields);
        }
    }

    /**
     * @param $id
     *
     * @return null|IblockSect
     */
    public function getById($id) {
        return $this->filter(function (IblockSect $element) use ($id) {
            return $element->getId() === $id;
        })->first();
    }
}
