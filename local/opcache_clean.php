<?php

/*
 * Очистка всего opcache. Должен дёргаться с токеном строго через HTTP
 */

if (!isset($_GET['token']) || $_GET['token'] !== '108cc6787c71f5038bd448b651eee59907f387e937e869ebc8168443eb4cddaf') {
    header('Forbidden', true, 403);
    die('Forbidden' . PHP_EOL);
}

if (opcache_reset()) {
    echo 'OpCache has been fully reset' . PHP_EOL;
} else {
    header('Internal server error', true, 500);
    echo 'OpCache disabled and can not be reset!' . PHP_EOL;
    die('Internal server error' . PHP_EOL);
}
