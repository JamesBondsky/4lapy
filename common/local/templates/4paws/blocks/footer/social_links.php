<?php

use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-social">
    <ul class="b-social__list">
        <li class="b-social__item">
            <a class="b-social__link" href="<?= tplvar('social_link_fb') ?>" title="Facebook" target="_blank">
                <span class="b-icon b-icon--fb">
                    <?= new SvgDecorator('icon-fb', 9, 18) ?>
                </span>
            </a>
            <?= tplinvis('social_link_fb') ?>
        </li>
        <li class="b-social__item">
            <a class="b-social__link" href="<?= tplvar('social_link_ok') ?>" title="Одноклассники" target="_blank">
                <span class="b-icon b-icon--ok">
                    <?= new SvgDecorator('icon-ok', 11, 18) ?>
                </span>
            </a>
            <?= tplinvis('social_link_ok') ?>
        </li>
        <li class="b-social__item">
            <a class="b-social__link" href="<?= tplvar('social_link_vk') ?>" title="ВКонтакте" target="_blank">
                <span class="b-icon b-icon--vk">
                    <?= new SvgDecorator('icon-vk', 22, 18) ?>
                </span>
            </a>
            <?= tplinvis('social_link_vk') ?>
        </li>
    </ul>
</div>
