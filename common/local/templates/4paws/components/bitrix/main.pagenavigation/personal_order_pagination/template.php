<?php use Bitrix\Main\Web\Uri;
use FourPaws\CatalogBundle\Service\CatalogLandingService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

/** @var PageNavigationComponent $component */
$component = $this->getComponent();

if ($arResult['END_PAGE'] <= 1) {
    return;
}

$this->setFrameMode(true);

$class = '';
if ($arParams['AJAX_MODE'] === 'Y') {
    $class = ' js-pagination';
} else {
    $class = ' js-orders-more';
}
?>

<div class="b-pagination order-page-nagivation">
    <ul class="b-pagination__list">
        <?php $disabled = (int)$arResult['CURRENT_PAGE'] === 1 ? 'b-pagination__item--disabled' : ''; ?>
        <li class="b-pagination__item b-pagination__item--prev <?= $disabled ?>">
            <?php if ((int)$arResult['CURRENT_PAGE'] > 1) { ?>
                <a class="b-pagination__link<?= $class ?>"
                   title="<?= $arResult['CURRENT_PAGE'] - 1 ?>"
                   href="javascript:void(0);" data-url="/ajax/personal/order/list/" data-page="<?= $arResult['CURRENT_PAGE'] > 2 ? $arResult['CURRENT_PAGE'] - 1 : $arResult['URL'] ?>">Назад</a>
            <?php } else { ?>
                <span class="b-pagination__link">Назад</span>
            <?php } ?>
        </li>
        <?php $page = 1;
        while ($page <= $arResult['PAGE_COUNT']):
            $url = $page >= 2 ? $component->replaceUrlTemplate($page) : $arResult['URL'];
            $uri = (new Uri($url))->deleteParams([CatalogLandingService::LANDING_REQUEST_KEY]);
            ?>
            <li class="b-pagination__item <?= $page === (int)$arResult['CURRENT_PAGE'] ? '' : $arResult['HIDDEN'][$page] ?? '' ?>">
                <a class="b-pagination__link<?= $class ?> <?= $page === (int)$arResult['CURRENT_PAGE'] ? 'active' : '' ?>"
                   title="<?= $page ?>"
                   href="javascript:void(0);" data-url="/ajax/personal/order/list/" data-page="<?= $page ?>"><?= $page ?></a>
            </li>
            <?php /** установка точек */
            if (($arResult['START_BETWEEN_BEGIN'] > 0 && $page === $arResult['START_BETWEEN_BEGIN'])
                || ($arResult['END_BETWEEN_BEGIN'] > 0 && $page === $arResult['END_BETWEEN_BEGIN'])) { ?>
                <li class="b-pagination__item">
                    <span class="b-pagination__dot">&hellip;</span>
                </li>
                <?php $page = $arResult['START_BETWEEN_BEGIN'] === $page ? $arResult['START_BETWEEN_END'] : $arResult['END_BETWEEN_END'];
            }
            $page++;
        endwhile; ?>
        <?php $disabled = (int)$arResult['CURRENT_PAGE'] === (int)$arResult['END_PAGE'] ? 'b-pagination__item--disabled' : ''; ?>
        <li class="b-pagination__item b-pagination__item--next <?= $disabled ?>">
            <?php if ((int)$arResult['CURRENT_PAGE'] < (int)$arResult['END_PAGE']) { ?>
                <a class="b-pagination__link<?= $class ?>"
                   title="<?= $arResult['CURRENT_PAGE'] + 1 ?>"
                   href="javascript:void(0);" data-url="/ajax/personal/order/list/" data-page="<?= $arResult['CURRENT_PAGE'] + 1 ?>">Вперед</a>
            <?php } else { ?>
                <span class="b-pagination__link">Вперед</span>
            <?php } ?>
        </li>
    </ul>
</div>
