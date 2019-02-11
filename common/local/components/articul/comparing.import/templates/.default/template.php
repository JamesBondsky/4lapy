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
    <h2>Выберите действие:</h2>
    <select name="type" class="js-type-select" style="width: 200px">
        <option value="import" <?=$_REQUEST['type'] == 'import' ? 'selected' : ''?>>Импорт</option>
        <option value="export" <?=$_REQUEST['type'] == 'export' ? 'selected' : ''?>>Экспорт</option>
    </select>

    <div class="area-import">
        <div class="adm-info-message-wrap">
            <div class="adm-info-message">
                <ul style="padding-left: 18px">
                    <li>Загружаемый файл должен быть в формате CSV с разделителем ";" и кодировкой windows-1251 или UTF-8, а также переводом строки в UNIX-стиле</li>
                    <li>Те эелементы, которые не присутствуют в файле импорта останутся без изменений</li>
                    <li>Товары сравниваются друг с другом в зависимости от группы сравнения. Одна группы - одна таблица сравнения</li>
                    <li>Если название группы отсутствует среди существующих - будет создана новая группа</li>
                </ul>
            </div>
        </div>

        <!--<span class="adm-input-file"><span>Добавить файл</span><input type="file" name="file" size="30" class="adm-designed-file"></span>-->
        <input type="file" name="file" size="30" class="adm-designed-file">
        <br>
        <button class="adm-btn adm-btn-save" style="margin-top: 30px">Импортировать</button>
    </div>

    <div class="area-export" style="display: none">
        <button class="adm-btn adm-btn-save"style="margin-top: 15px">Экспортировать</button>
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
            echo '<span style="color: #d88f21">Импорт прошёл частично, найдены следующие ошибки:</span> <br>';
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