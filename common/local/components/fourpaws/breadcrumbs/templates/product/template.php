<?php
/**
 * @var array $arParams
 * @var array $arResult
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<?php if (!empty($arResult['SECTIONS'])) { ?>
    <nav class="b-breadcrumbs">
        <ul class="b-breadcrumbs__list">
            <?php foreach ($arResult['SECTIONS'] as $section) { ?>
                <li class="b-breadcrumbs__item">
                    <a class="b-breadcrumbs__link"
                       href="<?= $section['SECTION_PAGE_URL'] ?>"
                       title="<?= $section['NAME'] ?>"><?= $section['NAME'] ?></a>
                </li>
            <?php } ?>
        </ul>
    </nav>
<?php } ?>
