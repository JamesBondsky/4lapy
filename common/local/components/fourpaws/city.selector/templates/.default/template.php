<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */

use Bitrix\Main\Page\Asset;
use FourPaws\Decorators\SvgDecorator;

// нужно для геолокации
Asset::getInstance()->addJs('https://api-maps.yandex.ru/2.1/?lang=ru_RU');

$this->setFrameMode(true);
?>

<div class="b-header__wrapper-for-popover">
    <a
            class="b-combobox b-combobox--header js-open-popover js-city-title"
            href="javascript:void(0);"
            title="<?php $frame = $this->createFrame()->begin($arResult['DEFAULT_CITY']['NAME'])?><?= $arResult['SELECTED_CITY']['NAME'] ?><?php $frame->end() ?>"
    >
        <span class="b-icon b-icon--location">
            <?= new SvgDecorator('icon-delivery-header', 14, 16) ?>
        </span>
        <span class="js-city-title">
            <?php $frame = $this->createFrame()->begin($arResult['DEFAULT_CITY']['NAME']) ?>
            <?= $arResult['SELECTED_CITY']['NAME'] ?>
            <?php $frame->end() ?>
        </span>
        <span class="b-icon b-icon--delivery-arrow">
            <?= new SvgDecorator('icon-arrow-down', 10, 13) ?>
        </span>
    </a>
    <div class="b-popover b-popover--blue-arrow js-your-city">
        <p class="b-popover__text">Ваш город&nbsp;&mdash;
            <span class="js-city-title">
                <?php $frame = $this->createFrame()->begin($arResult['DEFAULT_CITY']['NAME']) ?>
                <?= $arResult['SELECTED_CITY']['NAME'] ?>
                <?php $frame->end() ?>
            </span>?</p>
        <a class="b-popover__link" href="javascript:void(0)" title="">Да</a>
        <a class="b-popover__link b-popover__link--last" href="javascript:void(0)" title="">Нет, выбрать другой</a>
    </div>
</div>

<?php $frame = $this->createFrame()->begin('')?>
<script>
    var selectedCity = '<?= $arResult['SELECTED_CITY']['NAME'] ?>';
</script>
<?php $frame->end() ?>

<? /* todo fix this when markup is ready */ ?>
<div class="js-city-list" data-type="popular" style="display: none">
    <?php foreach ($arResult['POPULAR_CITIES'] as $city) { ?>
        <div data-name="<?= $city['NAME'] ?>" data-code="<?= $city['FIAS_CODE'] ?>">
            <?= $city['NAME'] ?>
        </div>
    <?php } ?>
</div>
<div class="js-city-list" data-type="popular" style="display: none">
    <?php foreach ($arResult['MOSCOW_CITIES'] as $city) { ?>
        <div data-name="<?= $city['NAME'] ?>" data-code="<?= $city['FIAS_CODE'] ?>">
            <?= $city['NAME'] ?>
        </div>
    <?php } ?>
</div>
