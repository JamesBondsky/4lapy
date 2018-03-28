<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-copyright">
    <div class="b-copyright__copyright">
        &copy; <?= (new DateTime())->format('Y') ?> Зоомагазин «Четыре Лапы»
    </div>
    <a class="b-copyright__link"
       href="/company/user-agreement/" title="Пользовательское соглашение">
        Пользовательское соглашение
    </a>
    <a class="b-copyright__link b-copyright__link--personal" href="/company/confidenciality/"
       title="Политика конфиденциальности">
        Политика конфиденциальности
    </a>
</div>
