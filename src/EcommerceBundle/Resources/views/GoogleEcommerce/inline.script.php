<?php

/**
 * @var string $data
 * @var bool $addScriptTag
 */

if ($addScriptTag) { ?>
    <script>
        window.dataLayer = window.dataLayer || [];
<?php } ?>
        dataLayer.push(<?= $data ?>);
<?php if ($addScriptTag) { ?>
    </script>
<?php }
