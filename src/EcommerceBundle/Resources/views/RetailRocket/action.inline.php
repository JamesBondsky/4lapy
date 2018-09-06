<?php

/**
 * @var string $action
 * @var string $value
 * @var bool   $isPush
 */

if ($isPush) { ?>
    (window["rrApiOnReady"] = window["rrApiOnReady"] || []).push(function () {
<?php } ?>
    try {
        rrApi.<?= $action ?>(<?= $value ?>);
    } catch (e) {
    }
<?php if ($isPush) { ?>
    })
<?php }
