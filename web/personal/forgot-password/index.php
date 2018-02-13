<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Восстановление пароля');

echo '<div class="b-registration b-registration--social b-registration--create-password js-registration-content">
    <header class="b-registration__header">
        <h1 class="b-title b-title--h1 b-title--registration">';
$APPLICATION->ShowTitle(false);
echo '</h1>
    </header>';
?>
<?php $APPLICATION->IncludeComponent(
    'fourpaws:forgotpassword',
    '',
    []
); ?>
<?php
echo '</div>
<div class="b-preloader b-preloader--fixed">
    <div class="b-preloader__spinner">
        <img class="b-preloader__image" src="/common/static/images/inhtml/spinner.svg" alt="spinner" title=""/>
    </div>
</div>';
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>