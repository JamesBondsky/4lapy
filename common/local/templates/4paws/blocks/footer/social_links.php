<?php

use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-social">
    <ul class="b-social__list">
        <li class="b-social__item">
            <a class="b-social__link" href="http://vk.com/4lapy_ru" title="ВКонтакте" target="_blank">
                <span class="b-icon b-icon--vk">
                    <?= new SvgDecorator('icon-vk', 22, 18) ?>
                </span>
            </a>
        </li>
        <li class="b-social__item">
            <a class="b-social__link" href="https://www.facebook.com/4laps" title="Facebook" target="_blank">
                <span class="b-icon b-icon--fb">
                    <?= new SvgDecorator('icon-fb', 9, 18) ?>
                </span>
            </a>
        </li>
        <li class="b-social__item">
            <a class="b-social__link" href="https://ok.ru/chetyre.lapy" title="Одноклассники" target="_blank">
                <span class="b-icon b-icon--ok">
                    <?= new SvgDecorator('icon-ok', 11, 18) ?>
                </span>
            </a>
        </li>
        <li class="b-social__item">
            <a class="b-social__link" href="https://www.youtube.com/channel/UCduvxcmOQFwTewukh9DUpvQ" title="Youtube" target="_blank">
                <span class="b-icon b-icon--youtube">
                    <?= new SvgDecorator('icon-youtube', 23, 23) ?>
                </span>
            </a>
        </li>
        <li class="b-social__item">
            <a class="b-social__link" href="https://www.instagram.com/4lapy.ru/" title="Instagram" target="_blank">
                <span class="b-icon b-icon--inst">
                    <?= new SvgDecorator('icon-inst', 23, 23) ?>
                </span>
            </a>
        </li>
    </ul>
</div>
