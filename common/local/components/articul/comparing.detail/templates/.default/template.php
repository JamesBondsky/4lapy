<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

IncludeTemplateLangFile(__FILE__);
?>

<div class="b-container">
    <h1 class="b-title b-title--block b-title--h1-compare b-title--h1-compare-detail"><?=$arResult['SECTION_HEADER']?></h1>

    <div class="b-table-feed-compare">
        <a href="/comparing/" class="b-table-feed-compare__btn">
            Выбрать другой корм
        </a>

        <div class="b-table-feed-compare__message" data-message-table-feed-compare="true">
            Поверните смартфон в&nbsp;горизонтальное положение для&nbsp;удобного просмотра таблицы
        </div>

        <div class="b-table-feed-compare__nav-mobile" data-nav-table-feed-compare="true"></div>

        <div class="b-table-feed-compare__content-wrap" data-wrap-table-feed-compare="true">
            <div class="b-table-feed-compare__content">
                <table class="b-table-feed-compare__table" data-table-feed-compare="true">
                    <tr class="b-table-feed-compare__row-products">
                        <td></td>
                        <? foreach($arResult['PRODUCTS'] as $arProduct): ?>
                        <td>
                            <div class="b-product-compare">
                                <div class="b-product-compare__image-wrap">
                                    <img class="b-product-compare__image" src="<?=$arResult['IMAGES'][$arProduct['IMAGE']]?>">
                                </div>
                                <div class="b-product-compare__descr">
                                    <?=$arProduct['NAME']?>
                                </div>
                                <div class="b-product-compare__characteristic">
                                    <span class="weight"><?=$arProduct['WEIGHT']?></span>
                                    <span>/ Арт. <?=$arProduct['PROPERTIES']['ARTICLE']['VALUE']?></span>
                                </div>
                            </div>
                        </td>
                        <? endforeach ?>
                        <td>
                        </td>
                    </tr>

                    <? foreach($arResult['PROPERTIES'] as $code => $arProperty): ?>
                    <? if(in_array($code, $this->getComponent()->hiddenProperties)) continue; ?>
                    <tr>
                        <td><?=$this->getComponent()->getPropertyName($arProperty)?></td>
                        <? foreach($arResult['PRODUCTS'] as $arProduct): ?>
                            <td><?=$this->getComponent()->getPropertyValue($arProduct['PROPERTIES'][$code])?></td>
                        <? endforeach ?>
                        <td><?=$this->getComponent()->getPropertyName($arProperty)?></td>
                    </tr>
                    <? endforeach ?>
                    <tr>
                        <td>Цена 1 порции</td>
                        <? foreach($arResult['PRODUCTS'] as $arProduct): ?>
                            <td><?=$arResult['PRICES'][$arProduct['PRODUCT_ID']]?> ₽</td>
                        <? endforeach ?>
                        <td>Цена 1 порции</td>
                    </tr>
                    <tr class="b-table-feed-compare__row-composition">
                        <td></td>
                        <? foreach($arResult['PRODUCTS'] as $arProduct): ?>
                        <td>
                            <div class="b-table-feed-compare__composition" data-composition-table-compare="true">
                                <div class="btn-composition" data-btn-composition-table-compare="true">
                                    Состав
                                </div>
                                <div class="content-composition" data-content-composition-table-compare="true">
                                    <?=$arProduct['PROPERTIES']['COMPOSITION']['VALUE']?>
                                </div>
                            </div>
                        </td>
                        <? endforeach ?>
                        <td></td>
                    </tr>
                </table>
            </div>
            <div class="b-table-feed-compare__mobile-composition" data-mobile-composition-table-compare="true">
                <div class="b-table-feed-compare__mobile-composition-inner" data-inner-mobile-composition-table-compare="true"></div>
            </div>
        </div>
    </div>
</div>


<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>