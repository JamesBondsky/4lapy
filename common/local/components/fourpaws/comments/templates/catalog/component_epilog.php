<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$uri = new \Bitrix\Main\Web\Uri($request->getRequestUri());
$fragment = $uri->getFragment();
if($fragment === 'new-review'){?>
<script>
    $(function(){
        $('div.b-tab-title__list a[data-tab=reviews]').click();
        $('div.b-tab-content div[data-tab-content=reviews] button.js-add-review').click();
    });
</script>
<?}