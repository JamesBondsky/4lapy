<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;

?>
<a class="b-adv-company" href="https://adv.ru/" title="Сделано в ADV" target="_blank">
    <span class="b-icon b-icon--adv">
        <?= new SvgDecorator('icon-logo-adv', 24, 24) ?>
    </span>
    Сделано в ADV
</a>
