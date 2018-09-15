<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

//delayed function must return a string
if (empty($arResult)) {
    return '';
}

$return = <<<END
    <nav class="b-breadcrumbs">
        <ul class="b-breadcrumbs__list" itemscope itemtype="http://schema.org/BreadcrumbList" >
END;

$positionContent = 0;
foreach ($arResult as $item) {
    $positionContent++;
    $return .= <<<END
            <li class="b-breadcrumbs__item"
                itemprop="itemListElement"
                itemscope
                itemtype="http://schema.org/ListItem" >
                <a class="b-breadcrumbs__link"
                   href="{$item['LINK']}"
                   title="{$item['TITLE']}"
                   itemtype="http://schema.org/Thing"
                   itemprop="item" ><span itemprop="name">{$item['TITLE']}</span></a>
                   <meta itemprop="position" content="{$positionContent}"/>
            </li>            
END;
}

$return .= <<<END
        </ul>
    </nav>
END;

return $return;
