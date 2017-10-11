<?php

namespace FourPaws\ProductAutoSort;

use FourPaws\ProductAutoSort\UserType\ElementPropertyConditionUserType;

class Event
{
    public static function init()
    {
        AddEventHandler(
            'main',
            'OnUserTypeBuildList',
            [ElementPropertyConditionUserType::class, 'getUserTypeDescription'],
            1000
        );
    }
}
