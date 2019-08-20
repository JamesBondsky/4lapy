<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Новая коллекция одежды для собак');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle("Новая коллекция одежды для собак");

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

?>
<div class="fashion-page">
    <section class="fashion-main-banner">
        <div class="fashion-main-banner__img"></div>
    </section>

    <section class="fashion-info">
        <div class="b-container">
            <div class="fashion-info__list">
                <div class="fashion-item-info fashion-item-info_small">
                    <div class="fashion-item-info__title">2&nbsp;000 товаров</div>
                    <div class="fashion-item-info__descr">из&nbsp;новой коллекции<br/> одежды и&nbsp;обуви</div>
                </div>
                <div class="fashion-item-info">
                    <div class="fashion-item-info__title">скидки до&nbsp;15%</div>
                    <div class="fashion-item-info__descr">при покупке <nobr>2-х</nobr> вещей&nbsp;&mdash; 7%,<br/> <nobr>3-х</nobr> вещей&nbsp;&mdash; 10%, <nobr>4-х</nobr>&nbsp;&mdash; 15%</div>
                </div>
                <div class="fashion-item-info fashion-item-info_full hide-xs">
                    <div class="fashion-item-info__title">бесплатная доставка и&nbsp;примерка</div>
                    <div class="fashion-item-info__descr">
                        <span>закажи несколько размеров<br/> домой или в&nbsp;магазин</span>
                        <span class="fashion-item-info__arr"></span>
                        <span>примерь и&nbsp;купи<br/> то, что подошло</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="fashion-total-look-section">
        <div class="fashion-total-look">
            <div class="fashion-total-look__slider"></div>
            <div class="fashion-total-look__list">
                <a href="#" class="item-fashion-total-look">
                    <div class="item-fashion-total-look__img"></div>
                    <div class="item-fashion-total-look__info">
                        <div class="item-fashion-total-look__title"><b>Petmax</b> Толстовка с сердцем с капюшоном и еще какой-то текст</div>
                        <div class="item-fashion-total-look__bottom">
                            <div class="item-fashion-total-look__size"></div>
                            <div class="item-fashion-total-look__price"></div>
                        </div>
                    </div>
                </a>
                <a href="#" class="item-fashion-total-look">
                    <div class="item-fashion-total-look__img"></div>
                    <div class="item-fashion-total-look__info">
                        <div class="item-fashion-total-look__title"><b>Petmax</b> Толстовка с сердцем с капюшоном и еще какой-то текст</div>
                        <div class="item-fashion-total-look__bottom">
                            <div class="item-fashion-total-look__size"></div>
                            <div class="item-fashion-total-look__price"></div>
                        </div>
                    </div>
                </a>
                <a href="#" class="item-fashion-total-look">
                    <div class="item-fashion-total-look__img"></div>
                    <div class="item-fashion-total-look__info">
                        <div class="item-fashion-total-look__title"><b>Petmax</b> Толстовка с сердцем с капюшоном и еще какой-то текст</div>
                        <div class="item-fashion-total-look__bottom">
                            <div class="item-fashion-total-look__size"></div>
                            <div class="item-fashion-total-look__price"></div>
                        </div>
                    </div>
                </a>
                <a href="#" class="item-fashion-total-look">
                    <div class="item-fashion-total-look__img"></div>
                    <div class="item-fashion-total-look__info">
                        <div class="item-fashion-total-look__title"><b>Petmax</b> Толстовка с сердцем с капюшоном и еще какой-то текст</div>
                        <div class="item-fashion-total-look__bottom">
                            <div class="item-fashion-total-look__size"></div>
                            <div class="item-fashion-total-look__price"></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="fashion-total-look">
            <div class="fashion-total-look__slider"></div>
            <div class="fashion-total-look__list">
                <a href="#" class="item-fashion-total-look">
                    <div class="item-fashion-total-look__img"></div>
                    <div class="item-fashion-total-look__info">
                        <div class="item-fashion-total-look__title"><b>Petmax</b> Толстовка с сердцем с капюшоном и еще какой-то текст</div>
                        <div class="item-fashion-total-look__bottom">
                            <div class="item-fashion-total-look__size"></div>
                            <div class="item-fashion-total-look__price"></div>
                        </div>
                    </div>
                </a>
                <a href="#" class="item-fashion-total-look">
                    <div class="item-fashion-total-look__img"></div>
                    <div class="item-fashion-total-look__info">
                        <div class="item-fashion-total-look__title"><b>Petmax</b> Толстовка с сердцем с капюшоном и еще какой-то текст</div>
                        <div class="item-fashion-total-look__bottom">
                            <div class="item-fashion-total-look__size"></div>
                            <div class="item-fashion-total-look__price"></div>
                        </div>
                    </div>
                </a>
                <a href="#" class="item-fashion-total-look">
                    <div class="item-fashion-total-look__img"></div>
                    <div class="item-fashion-total-look__info">
                        <div class="item-fashion-total-look__title"><b>Petmax</b> Толстовка с сердцем с капюшоном и еще какой-то текст</div>
                        <div class="item-fashion-total-look__bottom">
                            <div class="item-fashion-total-look__size"></div>
                            <div class="item-fashion-total-look__price"></div>
                        </div>
                    </div>
                </a>
                <a href="#" class="item-fashion-total-look">
                    <div class="item-fashion-total-look__img"></div>
                    <div class="item-fashion-total-look__info">
                        <div class="item-fashion-total-look__title"><b>Petmax</b> Толстовка с сердцем с капюшоном и еще какой-то текст</div>
                        <div class="item-fashion-total-look__bottom">
                            <div class="item-fashion-total-look__size"></div>
                            <div class="item-fashion-total-look__price"></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <section class="fashion-info hide show-xs">
        <div class="b-container">
            <div class="fashion-info__list">
                <div class="fashion-item-info fashion-item-info_full">
                    <div class="fashion-item-info__title">бесплатная доставка и&nbsp;примерка</div>
                    <div class="fashion-item-info__descr">
                        <span>закажи несколько размеров<br/> домой или в&nbsp;магазин</span>
                        <span class="fashion-item-info__arr"></span>
                        <span>примерь и&nbsp;купи<br/> то, что подошло</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="fashion-free-shipping">
        <div class="b-container">
            <div class="fashion-free-shipping__content">
                <div class="fashion-free-shipping__img"></div>
                <div class="fashion-free-shipping__info">
                    <div class="fashion-free-shipping__title">Закажи бесплатную доставку и&nbsp;примерку</div>
                    <ul class="fashion-free-shipping__steps">
                        <li>примерь несколько размеров</li>
                        <li>купи только то, что подошло</li>
                    </ul>
                    <div class="item-free-shipping">
                        <div class="item-free-shipping__title">домой</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_time"><b>время примерки</b> 15&nbsp;минут</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_delivery">
                            <p><b>доставка</b> от&nbsp;197Р бесплатно&nbsp;&mdash; при заказе от&nbsp;2000р</p>
                            <p>курьер привезёт ваш заказ в&nbsp;удобное место и&nbsp;время</p>
                        </div>
                    </div>
                    <div class="item-free-shipping">
                        <div class="item-free-shipping__title">в магазин</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_time"><b>время примерки</b> не ограничено</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_delivery">
                            <p><b>доставка</b> бесплатно</p>
                            <p>продавец поможет измерить питомца и&nbsp;быстро найти модель</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="fashion-interesting-clothes">
        <div class="b-container">
            <h2 class="fashion-title txt-center">Интересное про одежду</h2>
            <? $APPLICATION->IncludeComponent('fourpaws:items.list',
                'fashion',
                [
                    'ACTIVE_DATE_FORMAT'     => 'j F Y',
                    'AJAX_MODE'              => 'N',
                    'AJAX_OPTION_ADDITIONAL' => '',
                    'AJAX_OPTION_HISTORY'    => 'N',
                    'AJAX_OPTION_JUMP'       => 'N',
                    'AJAX_OPTION_STYLE'      => 'Y',
                    'CACHE_FILTER'           => 'Y',
                    'CACHE_GROUPS'           => 'N',
                    'CACHE_TIME'             => '36000000',
                    'CACHE_TYPE'             => 'A',
                    'CHECK_DATES'            => 'Y',
                    'FIELD_CODE'             => [
                        '',
                    ],
                    'FILTER_NAME'            => '',
                    'IBLOCK_ID'              => [
                        IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS),
                        IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES),
                    ],
                    'IBLOCK_TYPE'            => IblockType::PUBLICATION,
                    'NEWS_COUNT'             => '7',
                    'PREVIEW_TRUNCATE_LEN'   => '',
                    'PROPERTY_CODE'          => [
                        'PUBLICATION_TYPE',
                        'VIDEO',
                    ],
                    'SET_LAST_MODIFIED'      => 'N',
                    'SORT_BY1'               => 'ACTIVE_FROM',
                    'SORT_BY2'               => 'SORT',
                    'SORT_ORDER1'            => 'DESC',
                    'SORT_ORDER2'            => 'ASC',
                ],
                false,
                ['HIDE_ICONS' => 'Y']);
            ?>
        </div>
    </section>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>