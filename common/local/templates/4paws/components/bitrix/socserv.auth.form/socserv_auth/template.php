<?php

use Bitrix\Main\Text\HtmlFilter;
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 */?>

<ul class="b-registration__social-wrapper b-registration__social-wrapper--authorization">
    <?php
    if (\is_array($arParams['~AUTH_SERVICES']) && !empty($arParams['~AUTH_SERVICES'])) {
        foreach ($arParams['~AUTH_SERVICES'] as $service) {
            ?>
            <li class="b-social-block b-social-block--authorization">
                <a class="b-social-block__link js-social-reg"
                   id="bx_socserv_icon_<?= $service['ICON'] ?>"
                   href="javascript:void(0)"
                   onclick="<?= HtmlFilter::encode($service['ONCLICK']) ?? '' ?>"
                   title="<?= HtmlFilter::encode($service['NAME']) ?>"
                >
                <span class="b-icon b-icon--social b-icon--<?= $service['ICON'] ?>-registration b-icon--authorization">
                    <?= new SvgDecorator(
                        'icon-' . $service['ICON_DECORATOR']['CODE'],
                        $service['ICON_DECORATOR']['WIDTH'],
                        $service['ICON_DECORATOR']['HEIGHT']
                    ) ?>
                </span>
                </a>
            </li>
            <?php
        }
    }
    ?>
</ul>
