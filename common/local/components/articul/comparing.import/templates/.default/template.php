<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/*if($arResult['SUCCESS']){
    echo 'Импорт прошёл успешно';
}
else{
    echo 'Произошла ошибка при импорте: '.$arResult['ERROR'];
}*/

dump($arResult);

?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>