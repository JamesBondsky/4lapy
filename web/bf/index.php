<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
?>
<div class="page-blackfriday">
    <?php
    $APPLICATION->IncludeComponent(
        'articul:blackfriday.sections',
        '',
        [
            'CACHE_TIME' => 36000,
            'CACHE_TYPE' => 'Y',
        ],
        false
    );
    ?>
    
    <?php
    $APPLICATION->IncludeComponent(
        'articul:blackfriday.action.users',
        '',
        [
            'CACHE_TIME' => 36000,
            'CACHE_TYPE' => 'Y',
        ],
        false
    );
    ?>
    
    <?php
    $APPLICATION->IncludeComponent(
        'articul:blackfriday.questions',
        '',
        [
            'CACHE_TIME' => 36000,
            'CACHE_TYPE' => 'Y',
        ],
        false
    );
    ?>
</div>


<?php /*script>
    window.addEventListener('load', function() {
        var items = document.querySelectorAll('a.js-item-link, a.b-news-item__link');

        for (var i = 0; i < items.length; i++) {
            items[i].setAttribute('target', '_blank');
            items[i].target = '_blank';
        }
    });
</script */ ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
