<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arParams
 * @var array $arResult
 * @var array $templateData
 *
 * @var string $componentPath
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 *
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

use FourPaws\Decorators\SvgDecorator;

$this->setFrameMode(true);
?>
<section class="b-popup-pick-city js-popup-section" data-popup="pick-city">
    <?php $frame = $this->createFrame()->begin() ?>
    <div class="b-back-link b-back-link--pick-city">
        <a class="b-back-link__link js-close-popup"
           href="javascript:void(0);"
           title="<?= $arResult['SELECTED_CITY']['NAME'] ?>"
           data-url="<?= $arResult['CITY_SET_URL'] ?>"
           data-code="<?= $arResult['SELECTED_CITY']['CODE'] ?>">
            <span class="b-icon b-icon--back-mobile b-icon--orange">
                <?= new SvgDecorator('icon-arrow-down', 10, 10) ?>
            </span>
            <?= $arResult['SELECTED_CITY']['NAME'] ?>
        </a>
    </div>
    <?php $frame->beginStub() ?>
    <div class="b-back-link b-back-link--pick-city">
        <a class="b-back-link__link js-close-popup"
           href="javascript:void(0);"
           title="<?= $arResult['DEFAULT_CITY']['NAME'] ?>"
           data-url="<?= $arResult['CITY_SET_URL'] ?>"
           data-code="<?= $arResult['DEFAULT_CITY']['CODE'] ?>">
            <span class="b-icon b-icon--back-mobile b-icon--orange">
                <?= new SvgDecorator('icon-arrow-down', 10, 10) ?>
            </span>
            <?= $arResult['DEFAULT_CITY']['NAME'] ?>
        </a>
    </div>
    <?php $frame->end() ?>
    <header class="b-popup-pick-city__header">
        <form class="b-popup-pick-city__form" action="<?=$arResult['CITY_SET_URL'] ?>">
            <input class="b-input__input-field b-input__input-field--pick-city"
                   type="search"
                   id="id-pick-city-search"
                   data-url="<?= $arResult['CITY_AUTOCOMPLETE_URL'] ?>"
                   name="code"
                   placeholder="Найти свой город"/>
            <button class="b-button b-button--pick-city">
                <span class="b-icon">
                    <?= new SvgDecorator('icon-search', 16, 16) ?>
                </span>
            </button>
            <div class="b-popup-pick-city__autocomplete-wrapper" id="id-city-search"></div>
        </form>
        <a class="b-popup-pick-city__close js-close-popup" href="javascript:void(0)" title="закрыть"></a>
    </header>
    <main class="b-popup-pick-city__main" role="main">
        <div class="b-popup-pick-city__category-list">
            <a class="b-popup-pick-city__category active js-tab-city"
               href="javascript:void(0)"
               title="Москва и МО"
               data-tab-city="capital">Москва и МО</a>
            <a class="b-popup-pick-city__category js-tab-city"
               href="javascript:void(0)"
               title="Крупные города"
               data-tab-city="other">Крупные города</a>
        </div>
        <?php $frame = $this->createFrame()->begin() ?>
        <ul class="b-popup-pick-city__list-general active js-tab-content-city" data-content-city="capital">
            <?php foreach ($arResult['MOSCOW_CITIES'] as $letter => $cities) { ?>
                <li class="b-popup-pick-city__item-general">
                    <div class="b-popup-pick-city__litter"><?= $letter ?></div>
                    <ul class="b-popup-pick-city__list-litter">
                        <?php foreach ($cities as $city) { ?>
                            <?php $class = ($city['CODE'] == $arResult['SELECTED_CITY']['CODE']) ? 'b-popup-pick-city__city-link--active' : '' ?>
                            <li class="b-popup-pick-city__item-litter">
                                <a class="b-popup-pick-city__city-link <?= $class ?>"
                                   href="javascript:void(0)"
                                   title="<?= $city['NAME'] ?>"
                                   data-url="<?= $arResult['CITY_SET_URL'] ?>"
                                   data-code="<?= $city['CODE'] ?>">
                                    <?= $city['NAME'] ?>
                                    <?php if (!empty($city['SHOPS'])) { ?>
                                        <span class="b-icon b-icon--market">
                                            <?= new SvgDecorator('icon-pin', 13, 16) ?>
                                        </span>
                                    <?php } ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
        <ul class="b-popup-pick-city__list-general js-tab-content-city" data-content-city="other">
            <?php foreach ($arResult['POPULAR_CITIES'] as $letter => $cities) { ?>
                <li class="b-popup-pick-city__item-general">
                    <div class="b-popup-pick-city__litter"><?= $letter ?></div>
                    <ul class="b-popup-pick-city__list-litter">
                        <?php foreach ($cities as $city) { ?>
                            <?php $class = ($city['CODE'] == $arResult['SELECTED_CITY']['CODE']) ? 'b-popup-pick-city__city-link--active' : '' ?>
                            <li class="b-popup-pick-city__item-litter">
                                <a class="b-popup-pick-city__city-link <?= $class ?>"
                                   href="javascript:void(0)"
                                   title="<?= $city['NAME'] ?>"
                                   data-url="<?= $arResult['CITY_SET_URL'] ?>"
                                   data-code="<?= $city['CODE'] ?>">
                                    <?= $city['NAME'] ?>
                                    <?php if (!empty($city['SHOPS'])) { ?>
                                        <span class="b-icon b-icon--market">
                                            <?= new SvgDecorator('icon-pin', 13, 16) ?>
                                        </span>
                                    <?php } ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
        <?php $frame->beginStub() ?>
        <ul class="b-popup-pick-city__list-general active js-tab-content-city" data-content-city="capital">
            <?php foreach ($arResult['MOSCOW_CITIES'] as $letter => $cities) { ?>
                <li class="b-popup-pick-city__item-general">
                    <div class="b-popup-pick-city__litter"><?= $letter ?></div>
                    <ul class="b-popup-pick-city__list-litter">
                        <?php foreach ($cities as $city) { ?>
                            <?php $class = ($city['CODE'] == $arResult['DEFAULT_CITY']['CODE']) ? 'b-popup-pick-city__city-link--active' : '' ?>
                            <li class="b-popup-pick-city__item-litter">
                                <a class="b-popup-pick-city__city-link <?= $class ?>"
                                   href="javascript:void(0)"
                                   title="<?= $city['NAME'] ?>"
                                   data-url="<?= $arResult['CITY_SET_URL'] ?>"
                                   data-code="<?= $city['CODE'] ?>">
                                    <?= $city['NAME'] ?>
                                    <?php if (!empty($city['SHOPS'])) { ?>
                                        <span class="b-icon b-icon--market">
                                            <?= new SvgDecorator('icon-pin', 13, 16) ?>
                                        </span>
                                    <?php } ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
        <ul class="b-popup-pick-city__list-general js-tab-content-city" data-content-city="other">
            <?php foreach ($arResult['POPULAR_CITIES'] as $letter => $cities) { ?>
                <li class="b-popup-pick-city__item-general">
                    <div class="b-popup-pick-city__litter"><?= $letter ?></div>
                    <ul class="b-popup-pick-city__list-litter">
                        <?php foreach ($cities as $city) { ?>
                            <?php $class = ($city['CODE'] == $arResult['SELECTED_CITY']['CODE']) ? 'b-popup-pick-city__city-link--active' : '' ?>
                            <li class="b-popup-pick-city__item-litter">
                                <a class="b-popup-pick-city__city-link <?= $class ?>"
                                   href="javascript:void(0)"
                                   title="<?= $city['NAME'] ?>"
                                   data-url="<?= $arResult['CITY_SET_URL'] ?>"
                                   data-code="<?= $city['CODE'] ?>">
                                    <?= $city['NAME'] ?>
                                    <?php if (!empty($city['SHOPS'])) { ?>
                                        <span class="b-icon b-icon--market">
                                            <?= new SvgDecorator('icon-pin', 13, 16) ?>
                                        </span>
                                    <?php } ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
        <?php $frame->end() ?>
    </main>
    <footer class="b-popup-pick-city__footer">
        <div class="b-popup-pick-city__note-line">
            <span class="b-icon b-icon--market b-icon--market-static">
                <?= new SvgDecorator('icon-pin', 13, 16) ?>
            </span>
            <span class="b-popup-pick-city__note-text">– Наши зоомагазины в городе</span>
        </div>
    </footer>
</section>
