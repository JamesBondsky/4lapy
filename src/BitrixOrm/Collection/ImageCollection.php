<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\Image;

class ImageCollection extends D7CollectionBase
{

    /**
     * Извлечение модели
     */
    protected function fetchElement(): \Generator
    {
        while ($fields = $this->getResult()->fetch()) {
            yield new Image($fields);
        }
    }
}
