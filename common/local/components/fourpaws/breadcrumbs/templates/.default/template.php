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
        <?php if ($arParams['SHOW_LINK_TO_MAIN'] === 'Y') { ?>
            <li class="b-breadcrumbs__item">
                <a class="b-breadcrumbs__link" href="/"
                   title="Главная">Главная</a>
            </li>
        <?php } ?>
        <?php if (!empty($arResult['SECTIONS'])) { ?>
            <?php foreach ($arResult['SECTIONS'] as $section) { ?>
                <li class="b-breadcrumbs__item">
                    <a class="b-breadcrumbs__link"
                       href="<?= $section['SECTION_PAGE_URL'] ?>"
                       title="<?= $section['NAME'] ?>"><?= $section['NAME'] ?></a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
</nav>
