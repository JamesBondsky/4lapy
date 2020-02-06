<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<!-- CLEANTALK template addon -->
<?php \Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID("area"); if(CModule::IncludeModule("cleantalk.antispam")) echo CleantalkAntispam::FormAddon(); \Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID("area", "Loading..."); ?>
<!-- /CLEANTALK template addon -->

</body>
</html>