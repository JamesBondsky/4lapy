<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/* После оформления заказа показываем заново попап с адресом */
setcookie('show_address_popup', null, -1, '/');
