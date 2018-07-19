<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array    $arResult
 */

?>

<script>
    window.fourPawsErrorList = {
        errors: <?= CUtil::PhpToJSObject($arResult['ERRORS']) ?>,
        notices: <?= CUtil::PhpToJSObject($arResult['NOTICES']) ?>
    }
</script>
