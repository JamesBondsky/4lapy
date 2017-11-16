<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-social">
    <ul class="b-social__list">
        <li class="b-social__item">
            <a class="b-social__link" href="<?= tplvar('social_link_fb') ?>" title="Facebook" target="_blank">
                <span class="b-icon b-icon--fb">
                    <svg class="b-icon__svg" viewBox="0 0 9 18 " width="9px" height="18px">
                        <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-fb"></use>
                    </svg>
                </span>
            </a>
            <?= tplinvis('social_link_fb') ?>
        </li>
        <li class="b-social__item">
            <a class="b-social__link" href="<?= tplvar('social_link_ok') ?>" title="Одноклассники" target="_blank">
                <span class="b-icon b-icon--ok">
                    <svg class="b-icon__svg" viewBox="0 0 11 18 " width="11px" height="18px">
                        <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-ok"></use>
                    </svg>
                </span>
            </a>
            <?= tplinvis('social_link_ok') ?>
        </li>
        <li class="b-social__item">
            <a class="b-social__link" href="<?= tplvar('social_link_vk') ?>" title="ВКонтакте" target="_blank">
                <span class="b-icon b-icon--vk">
                    <svg class="b-icon__svg" viewBox="0 0 22 13 " width="22px" height="13px">
                        <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-vk"></use>
                    </svg>
                </span>
            </a>
            <?= tplinvis('social_link_vk') ?>
        </li>
    </ul>
</div>
