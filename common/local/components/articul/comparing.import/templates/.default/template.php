<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<script>
    $(function(){
        $('.js-type-select').on('change', function(){
            if($(this).val() == 'import'){
                $('.area-import').show();
                $('.area-export').hide();
            }
            else if($(this).val() == 'export'){
                $('.area-import').hide();
                $('.area-export').show();
            }
        });
    });
</script>

<form action="<?=$APPLICATION->GetCurPage()?>" method="POST" enctype="multipart/form-data">
    <select name="type" class="js-type-select" style="width: 200px; margin-bottom: 30px">
        <option value="import" <?=$_REQUEST['type'] == 'import' ? 'selected' : ''?>>Импорт</option>
        <option value="export" <?=$_REQUEST['type'] == 'export' ? 'selected' : ''?>>Экспорт</option>
    </select>

    <div class="area-import">
        <input type="file" name="file">
        <br>
        <button class="adm-btn adm-btn-save" style="margin-top: 15px">Импортировать</button>
    </div>

    <div class="area-export" style="display: none">
        <button class="adm-btn adm-btn-save">Экспортировать</button>
    </div>
</form>

<?
if(isset($arResult['SUCCESS'])){
    echo '<div style="margin-top: 30px;">';
    if(!$arResult['SUCCESS']){
        echo '<span style="color: red">В ходе импорта произошли ошибки:</span> <br>';
        echo implode('<br>', $arResult['ERRORS']);
    }
    else{
        if(!empty($arResult['ERRORS'])){
            echo '<span style="color: yellow">Импорт прошёл частично, найдены следующие ошибки:</span> <br>';
            echo implode('<br>', $arResult['ERRORS']);
        }
        else{
            echo '<span style="color: green">Импорт прошёл успешно!</span>';
        }

        echo '<br>'.implode('<br>', $arResult['EVENTS']);
    }
    echo '</div>';
}
?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>