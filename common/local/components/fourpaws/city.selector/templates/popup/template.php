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

use FourPaws\Decorators\SvgDecorator; ?>
<section class="b-popup-pick-city js-popup-section" data-popup="pick-city">
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
    <header class="b-popup-pick-city__header">
        <form class="b-popup-pick-city__form"
              action="<?= $arResult['CITY_SET_URL'] ?>"
              data-url="<?= $arResult['CITY_AUTOCOMPLETE_URL'] ?>"
              method="post">
            <div class="b-input b-input--pick-city">
                <input class="b-input__input-field b-input__input-field--pick-city"
                       type="search"
                       id="id-pick-city-search"
                       placeholder="Найти свой город"
                       name="id-pick-city-search"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
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
        <div class="b-popup-pick-city__category-list js-header-pick-city-scroll">
            <a class="b-popup-pick-city__category active js-tab-city"
               href="javascript:void(0)"
               title="Москва и МО"
               data-tab-city="capital">Москва и МО</a>
            <a class="b-popup-pick-city__category js-tab-city"
               href="javascript:void(0)"
               title="Крупные города"
               data-tab-city="other">Крупные города</a>
            <? if (isset($arResult['PERSONAL_ADDRESSES'])) { ?>
                <a class="b-popup-pick-city__category js-tab-city"
                   href="javascript:void(0)"
                   title="Из моих адресов"
                   data-tab-city="personal">Из моих адресов</a>
            <? } ?>
        </div>
        <ul class="b-popup-pick-city__list-general active js-tab-content-city" data-content-city="capital">
            <?php foreach ($arResult['MOSCOW_CITIES'] as $letter => $cities) { ?>
                <li class="b-popup-pick-city__item-general">
                    <div class="b-popup-pick-city__litter"><?= $letter ?></div>
                    <ul class="b-popup-pick-city__list-litter">
                        <?php foreach ($cities as $city) { ?>
                            <?php $class = ($city['CODE'] == $arResult['SELECTED_CITY']['CODE']) ? 'b-popup-pick-city__city-link--active' : '' ?>
                            <li class="b-popup-pick-city__item-litter">
                                <a class="b-popup-pick-city__city-link js-my-city <?= $class ?>"
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
            <?php foreach ($arResult['POPULAR_CITIES'] as $city) { ?>
                <li class="b-popup-pick-city__item-general">
                    <ul class="b-popup-pick-city__list-litter">
                        <?php $class = ($city['CODE'] == $arResult['SELECTED_CITY']['CODE']) ? 'b-popup-pick-city__city-link--active' : '' ?>
                        <li class="b-popup-pick-city__item-litter">
                            <a class="b-popup-pick-city__city-link js-my-city <?= $class ?>"
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
                    </ul>
                </li>
            <?php } ?>
        </ul>
        <? if (isset($arResult['PERSONAL_ADDRESSES'])) { ?>
            <ul class="b-popup-pick-city__list-general b-popup-pick-city__list-general--personal js-tab-content-city" data-content-city="personal">
                <? foreach ($arResult['PERSONAL_ADDRESSES'] as $address) { ?>
                    <li class="b-popup-pick-city__item-personal">
                        <a class="b-popup-pick-city__city-link js-my-city" href="javascript:void(0)" title="<?= $address->getCity() ?>" data-url="<?= $arResult['CITY_SET_URL'] ?>" data-code="<?= $address->getLocation() ?>">
                            <?= $address->getCity() ?>

                            <? if ($address->getHaveShop()) { ?>
                                <span class="b-icon b-icon--market">
                                    <?= new SvgDecorator('icon-pin', 13, 16) ?>
                                </span>
                            <? } ?>
                        </a>
                    </li>
                <? } ?>
            </ul>
        <? } ?>
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
