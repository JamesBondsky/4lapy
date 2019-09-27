<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Уютно жить');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle("Уютно жить");

use FourPaws\Decorators\SvgDecorator;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType; ?>

<div class="comfortable-living-page">
    <section class="promo-comfortable-living">
        <div class="b-container promo-comfortable-living__container">
            <div class="promo-comfortable-living__content">
                <div class="promo-comfortable-living__img-wrap">
                    <img src="/home/img/promo-comfortable-living.png" class="promo-comfortable-living__img">
                    <img src="/home/img/promo-comfortable-living_mobile.png" class="promo-comfortable-living__img promo-comfortable-living__img--mobile">
                </div>
                <ol class="promo-comfortable-living__list">
                    <li class="item">
                        <span class="bold">Покупай</span><br/> любые товары
                    </li>
                    <li class="item item_short">
                        <span class="bold">Копи марки</span><br/> 1&nbsp;марка за&nbsp;каждые 500Р&nbsp;в&nbsp;чеке
                    </li>
                    <li class="item">
                        <span class="bold">Меняй марки</span><br/> на&nbsp;скидки до&nbsp;30%
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <? $APPLICATION->IncludeComponent('articul:stamps.progress.bar', 'home', []) ?>

    <section class="products-comfortable-living" data-products-comfortable-living="true">
        <div class="b-container">
            <h2 class="title-comfortable-living">Уютный интерьер со скидкой -30%</h2>
        </div>
        <? $APPLICATION->IncludeComponent('articul:catalog.section.slider', 'stamps', ['SECTION_CODE' => 'stamps']) ?>
    </section>

    <section class="contest-comfortable-living">
        <div class="b-container contest-comfortable-living__container">
            <div class="contest-comfortable-living__content">
                <div class="contest-comfortable-living__info">
                    <div class="contest-comfortable-living__animals"></div>
                    <div class="contest-comfortable-living__boy"></div>
                    <div class="contest-comfortable-living__label"></div>

                    <div class="contest-comfortable-living__info-bottom">
                        <div class="contest-comfortable-living__title">
                            <span class="bold">супер-приз</span> победителю!
                        </div>
                        <div class="contest-comfortable-living__links">
                            <div class="contest-comfortable-living__links-item">
                                <a href="/home/img/draw.jpg" class="contest-comfortable-living__link-img" target="_blank" download="draw.jpg">
                                    <span>Скачать рисунок</span>
                                    <span class="b-icon">
                                    <?= new SvgDecorator('icon-download', 15, 14) ?>
                                </span>
                                </a>
                            </div>
                            <div class="contest-comfortable-living__links-item">
                                <a href="/home/img/Правила_Акции_Уютно_жить_октябрь2019.pdf" class="contest-comfortable-living__link-conditions" target="_blank">Подробные условия</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contest-comfortable-living__steps">
                    <div class="contest-comfortable-living__steps-title">Конкурс &laquo;Уютно жить&raquo;</div>
                    <ol class="contest-comfortable-living__steps-list">
                        <li class="item">Скачай и&nbsp;раскрась картинку</li>
                        <li class="item">Сфотографируйся с&nbsp;этой картинкой и&nbsp;своим питомцем</li>
                        <li class="item">Зарегистрируйся и&nbsp;загрузи фото</li>
                        <li class="item">Следи за&nbsp;итогами в&nbsp;социальных&nbsp;сетях</li>
                    </ol>
                    <div class="contest-comfortable-living__steps-panel">
                        <?if ($USER->IsAuthorized()) {?>
                            <form class="contest-comfortable-living__form" enctype="multipart/form-data" method="post" action="/ajax/landing/home/add" data-form-photo-comfortable-living-landing="true">
                                <div class="contest-comfortable-living__steps-btn">
                                    <span>Загрузить фото</span>
                                    <input class="contest-comfortable-living__photo" type="file" name="PHOTO" accept="image/*, image/jpeg, image/png" data-photo-comfortable-living-landing="true">
                                </div>
                            </form>
                        <? } else { ?>
                            <div class="contest-comfortable-living__steps-btn js-open-popup" data-popup-id="authorization">Зарегистрироваться</div>
                        <? } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="info-comfortable-living">
        <div class="b-container">
            <h2 class="title-comfortable-living">Как накопить марки и купить домик, лежак или когтеточку со скидкой до - 30%</h2>
             <div class="info-comfortable-living__content">
                <div class="info-comfortable-living__img-wrap">
                    <div class="info-comfortable-living__img" style="background-image: url('/home/img/steps-info.jpg')"></div>
                </div>
                 <ol class="info-comfortable-living__steps">
                     <li class="item">Совершай любые покупки, копи марки в&nbsp;буклете
                         или Личном кабинете: 1&nbsp;<span class="b-icon b-icon--mark"><?= new SvgDecorator('icon-mark', 24, 24) ?></span>&nbsp;=&nbsp;500&nbsp;Р</li>
                     <li class="item">Отслеживай баланс марок: на&nbsp;чеке, в&nbsp;буклете <a href="/personal/marki/" target="_blank">в&nbsp;личном&nbsp;кабинете</a> и&nbsp;в&nbsp;приложении</li>
                     <li class="item">
                         Покупай лежаки и&nbsp;когтеточки со&nbsp;скидкой до&nbsp;-30%

                         <ul class="item__list">
                             <li>&mdash;&nbsp;на&nbsp;сайте и&nbsp;в&nbsp;приложении: добавь товар в&nbsp;корзину, нажми &laquo;списать марки&raquo;</li>
                             <li>&mdash;&nbsp;в&nbsp;магазине: предъяви буклет или сообщи кассиру номер телефона</li>
                         </ul>
                     </li>
                 </ol>
             </div>
        </div>
    </section>

    <? $APPLICATION->IncludeComponent('articul:home.faq', '', []) ?>

    <section class="articles-comfortable-living">
        <div class="b-container">
            <h2 class="title-comfortable-living">Полезные статьи</h2>
            <div class="articles-comfortable-living__content">
                <?
                $section = CIBlockSection::GetList([], ['CODE' => 'home'])->Fetch();
                if($section) {
                    global $arNewsFilter;
                    $arNewsFilter = ['SECTION_ID' => [$section['ID']]];

                    $APPLICATION->IncludeComponent('fourpaws:items.list',
                        'home',
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
                            'FILTER_NAME'            => 'arNewsFilter',
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
                        ['HIDE_ICONS' => 'Y']
                    );
                }
                ?>
            </div>

        </div>
    </section>
</div>

<script>
    window.addEventListener('load', function() {
        var items = document.querySelectorAll('a.js-item-link, a.b-news-item__link');

        for (var i = 0; i < items.length; i++) {
            items[i].setAttribute('target', '_blank');
            items[i].target = '_blank';
        }
    });
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
