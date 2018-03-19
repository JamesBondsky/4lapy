<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 */

\Bitrix\Main\Page\Asset::getInstance()->addJs('https://cdnjs.cloudflare.com/ajax/libs/dot/1.1.2/doT.min.js');

?>

<div class="delivery-intervals-edit"></div>
<script>
    var deliveryIntervalsComponentMountPoint = '.delivery-intervals-edit';
    var deliveryIntervalsComponentData = <?= CUtil::PhpToJSObject($arParams['VALUE']); ?>;
    var deliveryIntervalsInputName = '<?= $arParams['INPUT_NAME'] ?>';
</script>
