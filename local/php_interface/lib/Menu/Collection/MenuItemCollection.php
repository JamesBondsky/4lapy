<?php

namespace FourPaws\Menu\Collection;

use FourPaws\BitrixIblockORM\Collection\CollectionBase;
use FourPaws\Menu\Model\MenuItem;

class MenuItemCollection extends CollectionBase
{
    /**
     * @return MenuItem|false
     */
    protected function doFetch()
    {
        //TODO Вообще-то этот код для меню пока не используется. Нужен ли он?

        $fields = $this->getCDBResult()->GetNext();

        if (false == $fields) {
            return false;
        }

        return new MenuItem($fields);

    }

}
