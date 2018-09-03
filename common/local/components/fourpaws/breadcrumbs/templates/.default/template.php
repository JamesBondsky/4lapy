<?php
/**
 * @var array $arParams
 * @var array $arResult
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<nav class="b-breadcrumbs <?= $arParams['ADDITIONAL_CLASS'] ?? '' ?>">
    <ul class="b-breadcrumbs__list">
        <?php if ($arParams['IS_LANDING']) { ?>
            <li class="b-breadcrumbs__item">
                <a class="b-counter-basket__basket-link" style="position: static;width: auto;" href="<?= $arResult['BACK_LINK'] ?>" title="Вернуться в каталог">Вернуться в каталог</a>
            </li>
        <?php } else {
            if ($arParams['SHOW_LINK_TO_MAIN'] === 'Y' && !$arParams['IS_LANDING']) { ?>
                <li class="b-breadcrumbs__item">
                    <a class="b-breadcrumbs__link" href="/" title="Главная">Главная</a>
                </li>
            <?php }

            if ($arParams['IS_CATALOG'] && !$arParams['IS_LANDING']) { ?>
                <li class="b-breadcrumbs__item">
                    <a class="b-breadcrumbs__link" href="/catalog/" title="Каталог">Каталог</a>
                </li>
            <?php }

            foreach ($arResult['SECTIONS'] as $k => $section) { ?>
                <li class="b-breadcrumbs__item">
                    <a class="b-breadcrumbs__link"
                       href="<?= $section['SECTION_PAGE_URL'] ?>"
                       title="<?= $section['NAME'] ?>"><?= $section['NAME'] ?></a>
                </li>
            <?php }
        } ?>
    </ul>
</nav>
