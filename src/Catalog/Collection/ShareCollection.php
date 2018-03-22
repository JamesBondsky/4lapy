<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixOrm\Collection\IblockElementCollection;
use FourPaws\Catalog\Model\Share;
use Generator;

class ShareCollection extends IblockElementCollection
{
    /**
     * @inheritdoc
     */
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new Share($fields);
        }
    }
}
