<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-copyright">
    <div class="b-copyright__copyright">
        &copy; <?= (new DateTime())->format('Y') ?> Зоомагазин «Четыре лапы»
    </div>
    <a class="b-copyright__link"
       href="/company/user-agreement/" title="Пользовательское соглашение">
        Пользовательское соглашение
    </a>
    <a class="b-copyright__link b-copyright__link--personal" href="/company/privacy-policy/"
       title="Условия использования персональных данных">
        Условия использования персональных данных
    </a>
</div>
