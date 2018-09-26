<?php
/**
 * @var array $arParams
 * @var array $arResult
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$positionContent = 0;
//TODO Заменить на использование стандартной цепочки навигации и убрать этот дублирующий фрагмент кода
?>
<nav class="b-breadcrumbs <?= $arParams['ADDITIONAL_CLASS'] ?? '' ?>">
    <ul class="b-breadcrumbs__list" itemscope itemtype="http://schema.org/BreadcrumbList" >
        <?php if ($arParams['SHOW_LINK_TO_MAIN'] === 'Y' && !$arParams['IS_LANDING']) { ?>
            <li class="b-breadcrumbs__item"
                itemprop="itemListElement"
                itemscope
                itemtype="http://schema.org/ListItem">
                <a class="b-breadcrumbs__link"
                   href="/"
                   title="Главная"
                   itemtype="http://schema.org/Thing"
                   itemprop="item"><span itemprop="name">Главная</span></a>
                <meta itemprop="position" content="<?= ++$positionContent ?>"/>
            </li>
        <?php }

        if ($arParams['IS_CATALOG'] && !$arParams['IS_LANDING']) { ?>
            <li class="b-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                <a class="b-breadcrumbs__link"
                   href="/catalog/"
                   title="Каталог"
                   itemtype="http://schema.org/Thing"
                   itemprop="item"><span itemprop="name">Каталог</span></a>
                <meta itemprop="position" content="<?= ++$positionContent ?>"/>
            </li>
        <?php }

        foreach ($arResult['SECTIONS'] as $k => $section) {
            if (!$k && $arParams['IS_LANDING']) {
                continue;
            } ?>
            <li class="b-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                <a class="b-breadcrumbs__link"
                   href="<?= $section['URL'] ?>"
                   title="<?= $section['NAME'] ?>"
                   itemtype="http://schema.org/Thing"
                   itemprop="item"><span itemprop="name"><?= $section['NAME'] ?></span></a>
                <meta itemprop="position" content="<?= ++$positionContent ?>"/>
            </li>
        <?php } ?>
    </ul>
</nav>
