<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die;
}
/**
 * @var array $arResult
 */
?>

<script>
    $(document).ready(function () {
        $('.js-exchange-rule-<?= $arResult['CURRENT_STAMP_LEVEL'] ?>').each(function (key, item) {
            $(item).addClass('is-active');
        });
    });
</script>
