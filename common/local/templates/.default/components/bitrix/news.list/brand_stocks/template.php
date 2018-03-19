<?php use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Список элементов в разделах: Акции, Новости, Статьи
 *
 * @updated: 01.01.2018
 */

$this->setFrameMode(true);
if (empty($arResult['ITEMS'])) {
    return;
} ?>
    <section class="b-common-section">
        <div class="b-common-section__title-box b-common-section__title-box--one-brand-catalog">
            <h2 class="b-title b-title--one-brand-catalog"><a href="javascript:void(0);" title="Акции">Акции</a></h2>
        </div>
        <div class="b-common-section__content b-common-section__content--one-brand-catalog js-catalog-one-brand">
            <?php foreach ($arResult['ITEMS'] as $arItem) {
                $this->AddEditAction(
                    $arItem['ID'],
                    $arItem['EDIT_LINK'],
                    \CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT')
                );
                $this->AddDeleteAction(
                    $arItem['ID'],
                    $arItem['DELETE_LINK'],
                    \CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'),
                    [
                        'CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM'),
                    ]
                ); ?>
                <article class="b-news-item">
                    <a class="b-news-item__link" href="<?= $arItem['DETAIL_PAGE_URL'] ?>"
                       title="Мастер-классы – встречи друзей!" id="<?= $this->GetEditAreaId($arItem['ID']) ?>">
                        <?php if (!empty($arItem['PRINT_PICTURE']['SRC'])) { ?>
                            <span class="b-news-item__image-wrapper js-image-cover">
                    <img class="b-news-item__image" src="<?= $arItem['PRINT_PICTURE']['SRC'] ?>"
                         alt="<?= $arItem['PRINT_PICTURE']['ALT'] ?>" title="<?= $arItem['PRINT_PICTURE']['TITLE'] ?>">
                </span>
                        <?php } ?>
                        <span class="b-news-item__label">акция</span>
                        <h4 class="b-news-item__header"><?= $arItem['NAME'] ?></h4>
                        <?php if (isset($arParams['DISPLAY_PREVIEW_TEXT']) && $arParams['DISPLAY_PREVIEW_TEXT'] === 'Y') { ?>
                            <p class="b-news-item__description"><?= htmlspecialcharsback($arItem['PREVIEW_TEXT']); ?></p>
                        <?php } ?>
                        <?php if (!empty($arItem['DISPLAY_ACTIVE_FROM'])) { ?>
                            <span class="b-news-item__date"><?= ToLower($arItem['DISPLAY_ACTIVE_FROM']) ?></span>
                        <?php } ?>
                    </a>
                </article>
            <?php } ?>
        </div>
    </section>
<?php if ($arParams['DISPLAY_BOTTOM_PAGER']) {
    echo $arResult['NAV_STRING'];
}
