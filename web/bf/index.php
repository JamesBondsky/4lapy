<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
?>
<div class="page-blackfriday">
    <section class="main-banner-blackfriday">
        <div class="b-container">
            <div class="main-banner-blackfriday__info">
                <div class="main-banner-blackfriday__title main-banner-blackfriday__title_desktop">до&nbsp;-50% на&nbsp;2&nbsp;500 товаров</div>
                <div class="main-banner-blackfriday__date">с&nbsp;28 ноября по&nbsp;1 декабря</div>
                <div class="timer-blackfriday">
                    <div class="timer-blackfriday__title">До&nbsp;завершения <span class="hide-mobile">акции</span> осталось:</div>
                    <div class="timer-blackfriday__time"></div>
                </div>
                <div class="main-banner-blackfriday__title main-banner-blackfriday__title_mobile">до&nbsp;-50% на&nbsp;2&nbsp;500 товаров</div>
            </div>
        </div>
    </section>
    
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
