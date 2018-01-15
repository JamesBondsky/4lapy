<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

/** @var PageNavigationComponent $component */
$component = $this->getComponent();

if($arResult['END_PAGE'] <= 1){
    return;
}

$this->setFrameMode(true);
?>

<div class="b-pagination">
    <ul class="b-pagination__list">
        <li class="b-pagination__item b-pagination__item--prev <?= ((int)$arResult['CURRENT_PAGE']
                                                                    === 1) ? '' : 'b-pagination__item--disabled' ?>">
            <?php if ((int)$arResult['CURRENT_PAGE'] > 1) { ?>
                <a class="b-pagination__link  js-pagination"
                   title="<?= $arResult['CURRENT_PAGE'] - 1 ?>"
                   href="<?= $arResult['CURRENT_PAGE'] > 2 ? htmlspecialcharsbx(
                       $component->replaceUrlTemplate(
                           $arResult['CURRENT_PAGE'] - 1
                       )
                   ) : $arResult['URL'] ?>">Назад</a>
                <?php
            } else {
                ?>
                <span class="b-pagination__link">Назад</span>
                <?php
            } ?>
        </li>
        
        <?php
        $page = $arResult['START_PAGE'];
        while ($page <= $arResult['END_PAGE']):?>
            <?php if ((int)$page === (int)$arResult['CURRENT_PAGE']): ?>
                <li class="b-pagination__item">
                    <a class="b-pagination__link js-pagination active"
                       href="javascript:void(0);"
                       title="<?= $page ?>"><?= $page ?></a>
                </li>
            <? else: ?>
                <li class="b-pagination__item <?= $arResult['HIDDEN'][$page] ?? '' ?>">
                    <a class="b-pagination__link js-pagination"
                       href="<?= $page > 2 ? htmlspecialcharsbx(
                           $component->replaceUrlTemplate(
                               $page
                           )
                       ) : $arResult['URL'] ?>"
                       title="<?= $page ?>"><?= $page ?></a>
                </li>
            <? endif;
            
            if ($page === 1 && (int)$arResult['START_PAGE'] > 1
                && (int)$arResult['START_PAGE'] - $page >= 0) {
                ?>
                <li class="b-pagination__item">
                    <span class="b-pagination__dot">&hellip;</span>
                </li><?php
                $page = (int)$arResult['START_PAGE'];
            } elseif ($page === (int)$arResult['END_PAGE']
                      && (int)$arResult['END_PAGE'] > ($arResult['END_PAGE'] - 1)) {
                ?>
                <li class="b-pagination__item">
                    <span class="b-pagination__dot">&hellip;</span>
                </li><?php
                $page = $arResult['NavPageCount'];
            } else {
                $page++;
            }
            ?>
        <? endwhile ?>
        
        <li class="b-pagination__item b-pagination__item--next <?= ((int)$arResult['CURRENT_PAGE']
                                                                    === (int)$arResult['END_PAGE']) ? '' : 'b-pagination__item--disabled' ?>">
            <?php if ((int)$arResult['CURRENT_PAGE'] < (int)$arResult['END_PAGE']) { ?>
                <a class="b-pagination__link js-pagination"
                   title="<?= $arResult['CURRENT_PAGE'] + 1 ?>"
                   href="<?= htmlspecialcharsbx(
                       $component->replaceUrlTemplate($arResult['CURRENT_PAGE'] + 1)
                   ) ?>">
                    Вперед
                </a>
                <?php
            } else {
                ?>
                <span class="b-pagination__link">Вперед</span>
                <?php
            } ?>
        </li>
    </ul>
</div>
