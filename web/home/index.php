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

    <section class="contest-comfortable-living">
        <div class="b-container">
            <h2 class="title-comfortable-living">Как накопить марки и купить домик, лежак или когтеточку со скидкой до - 30%</h2>
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

    <section class="questions-comfortable-living">
        <div class="b-container">
            <h2 class="title-comfortable-living title-comfortable-living_questions">Вопросы и ответы</h2>
            <div class="questions-comfortable-living__accordion">
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Как накопить марки?</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                Покупай Taft в магазинах сети «Лента» с 1 по 30 сентября и получай
                                гарантированно 30 баллов на карту лояльности, а также участвуй
                                в розыгрыше Beauty Box.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Какие будут подарки</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                Покупай Taft в магазинах сети «Лента» с 1 по 30 сентября и получай
                                гарантированно 30 баллов на карту лояльности, а также участвуй
                                в розыгрыше Beauty Box.
                            </div>
                            <div class="item-accordion__block-img">
                                <img src="/home/img/questions.png" alt="" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Как принять участие</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                Покупай Taft в магазинах сети «Лента» с 1 по 30 сентября и получай
                                гарантированно 30 баллов на карту лояльности, а также участвуй
                                в розыгрыше Beauty Box.
                            </div>
                            <div class="item-accordion__block-img">
                                <img src="/home/img/questions.png" alt="" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Ещё какие-то вопросы</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                Покупай Taft в магазинах сети «Лента» с 1 по 30 сентября и получай
                                гарантированно 30 баллов на карту лояльности, а также участвуй
                                в розыгрыше Beauty Box.
                            </div>
                            <div class="item-accordion__block-img">
                                <img src="/home/img/questions.png" alt="" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="articles-comfortable-living">
        <div class="b-container">
            <h2 class="title-comfortable-living">Полезные статьи</h2>
            <div class="articles-comfortable-living__content">
                <?
                $APPLICATION->IncludeComponent('fourpaws:items.list',
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
                ?>
            </div>

        </div>
    </section>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
