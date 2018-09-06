<?php

/**
 * @var string $data
 * @var string $presetName
 * @var bool $addScriptTag
 */

if ($addScriptTag) { ?>
    <script>
        <?php } ?>
        window.<?=$presetName?>=<?= $data ?>;
        <?php if ($addScriptTag) { ?>
    </script>
<?php }
