<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var \CMain $APPLICATION */

use FourPaws\Decorators\SvgDecorator; ?>
<div class="b-header-info__item b-header-info__item--phone">
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:city.phone',
        'template.header.popover',
        [],
        false,
        ['HIDE_ICONS' => 'Y']
    ) ?>
    <div class="b-popover b-popover--phone js-popover">
        <div class="b-contact">
            <?php $APPLICATION->IncludeComponent(
                'fourpaws:city.phone',
                'template.header',
                [],
                false,
                ['HIDE_ICONS' => 'Y']
            ) ?>
            <dl class="b-phone-pair">
                <dt class="b-phone-pair__phone">
                    <a class="b-phone-pair__link"
                       href="tel:<?= preg_replace('~[^+\d]~', '', tplvar('phone_main')) ?>"
                       title="<?= tplvar('phone_main') ?>">
                        <?= tplvar('phone_main') ?>
                    </a>
                    <?= tplinvis('phone_main') ?>
                </dt>
                <dd class="b-phone-pair__description"><?= tplvar('phone_sign', true) ?></dd>
            </dl>
            <ul class="b-link-block b-link-block--border">
                <li class="b-link-block__item">
                    <a class="b-link-block__link" href="javascript:void(0);" title="Перезвоните мне">
                        <span class="b-icon">
                            <?= new SvgDecorator('icon-phone-header', 16, 16) ?>
                        </span>
                        Перезвоните мне
                    </a>
                </li>
                <li class="b-link-block__item">
                    <a class="b-link-block__link" href="javascript:void(0);" title="Обратная связь">
                        <span class="b-icon">
                            <?= new SvgDecorator('icon-email-header', 16, 16) ?>
                        </span>
                        Обратная связь
                    </a>
                </li>
                <li class="b-link-block__item">
                    <a class="b-link-block__link" href="javascript:void(0);" title="Чат с консультантом">
                        <span class="b-icon">
                            <?= new SvgDecorator('icon-chat-header', 16, 16) ?>
                        </span>
                        Чат с консультантом
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
