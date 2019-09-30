<?php

use FourPaws\Decorators\SvgDecorator;
use FourPaws\PersonalBundle\Service\StampService;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */
?>

<?php
$APPLICATION->IncludeComponent('articul:stamps.progress.bar', 'personal', []);
?>

<?php
$APPLICATION->IncludeComponent('articul:catalog.section.slider', 'stamps', [
    'SECTION_CODE' => 'stamps'
]);
?>

<section class="info-comfortable-living">
    <div class="b-container" style="max-width: 100%;">
        <h2 class="title-comfortable-living">Как накопить марки и купить домик, лежак или когтеточку со скидкой до - 30%</h2>
        <div class="info-comfortable-living__content">
            <div class="info-comfortable-living__img-wrap">
                <div class="info-comfortable-living__img" style="background-image: url('/home/img/steps-info.jpg')"></div>
            </div>
            <ol class="info-comfortable-living__steps">
                <li class="item">Совершай любые покупки, копи марки в&nbsp;буклете
                    или Личном кабинете: 1&nbsp;<span class="b-icon b-icon--mark"><?= new SvgDecorator('icon-mark', 24, 24) ?></span>&nbsp;=&nbsp;<?= StampService::MARK_RATE ?>&nbsp;Р
                </li>
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
