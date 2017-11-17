<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator; ?>
<div class="b-header-info__item b-header-info__item--phone">
    <a class="b-header-info__link js-open-popover" href="javascript:void(0);" title="+7 473 202-76-26">
        <span class="b-icon">
            <?= new SvgDecorator('icon-phone-dark', 16, 16) ?>
        </span>
        <span class="b-header-info__inner">+7 473 202-76-26</span>
        <span class="b-icon b-icon--header b-icon--left-3">
            <?= new SvgDecorator('icon-arrow-down', 10, 12) ?>
        </span>
    </a>
    <div class="b-popover b-popover--phone js-popover">
        <div class="b-contact">
            <? /** @todo вынести в телефон */ ?>
            <dl class="b-phone-pair">
                <dt class="b-phone-pair__phone">
                    <a class="b-phone-pair__link" href="tel:84732027626" title="+7 473 202-76-26">
                        +7 473 202-76-26
                    </a>
                </dt>
                <dd class="b-phone-pair__description">Для Нижнего Новгорода. Доступен до 21:00</dd>
            </dl>
            <dl class="b-phone-pair">
                <dt class="b-phone-pair__phone">
                    <a class="b-phone-pair__link"
                       href="tel:<?= preg_replace('[^+\d]', '', tplvar('phone_main')) ?>"
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
