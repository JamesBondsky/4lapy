<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$title = 'Регистрация';
if(\FourPaws\KioskBundle\Service\KioskService::isKioskMode()){
    $title = 'Мы Вас не узнали! Зарегистрируйтесь?';
}
$APPLICATION->SetTitle($title);

echo '<div class="b-registration b-registration--two-parts js-registration-content">
    <header class="b-registration__header">
        <h1 class="b-title b-title--h1 b-title--registration">';
$APPLICATION->ShowTitle(false);
echo '</h1>
    </header>';
?>
<?$APPLICATION->IncludeComponent(
    'fourpaws:register',
    '',
    Array()
);?>
<?php
echo '</div>
<div class="b-preloader b-preloader--fixed">
    <div class="b-preloader__spinner">
        <img class="b-preloader__image" src="/static/build/images/inhtml/spinner.svg" alt="spinner" title=""/>
    </div>
</div>';
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>