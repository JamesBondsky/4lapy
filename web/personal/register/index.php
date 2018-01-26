<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Регистрация');

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
echo '</div>';
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>