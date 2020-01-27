<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-copyright js-copyright-scroll">
    <div class="b-copyright__copyright" data-test="test">
        &copy; <?= (new DateTime())->format('Y') ?> Зоомагазин «Четыре Лапы»
    </div>
    <a class="b-copyright__link"
       href="/user-agreement/" title="Пользовательское соглашение">
        Пользовательское соглашение
    </a>
    <a class="b-copyright__link b-copyright__link--personal" href="/confidenciality/"
       title="Политика конфиденциальности">
        Политика конфиденциальности
    </a>
</div>
