<?php

use Bitrix\Main\Grid\Declension;
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

$marksDeclension = new Declension('марку', 'марки', 'марок');

?>
<div class="b-kopilka b-kopilka--exchange-discount">
    <h2 class="b-title b-kopilka__title">Марки</h2>

    <img src="kek">
    <div class="b-kopilka__details">
        <div class="b-kopilka__ticket-mark">
            <div>Мои марки</div>
            <div class="title-ticket-mark"><?= $arResult['ACTIVE_STAMPS_COUNT'] ?></div>
            <div class="descr-ticket-mark">
               Моя скидка -
            </div>
        </div>
        <div class="b-kopilka__info">
            <h3>Вместе за парту!</h3>
            <p>Наступает осенняя пора, дети идут в школу, начинаются учебные будни. Вы можете вместе с питомцем тоже начать учиться</p>
            <h3>Условия акции</h3>
            <p>1. Делай покупки, получай марки: 1 марка  = <?= StampService::MARK_RATE ?> руб.;</p>
            <p>2. Отслеживай марки где удобно:<br>
                на чеке, в личном кабинете на сайте и в приложении;</p>
            <p>3. Выбери игру и добавь в корзину, нажми "списать марки";</p>
            <p>4. Получи игру со скидкой и развивай питомца!</p>
            <a href="/iqgames/">Подробные условия акции</a>
        </div>
    </div>

    <div class="b-kopilka__details">
        <div class="b-kopilka__ticket-mark">
            <div class="title-ticket-mark"><?= $arResult['ACTIVE_STAMPS_COUNT'] . ' ' . $marksDeclension->get($arResult['ACTIVE_STAMPS_COUNT']) ?></div>
            <div class="descr-ticket-mark">
                Период начисления марок <nobr>1.09.19</nobr>&nbsp;&mdash; <nobr>30.09.19</nobr>
            </div>
        </div>
        <div class="b-kopilka__info">
	        <h3>Вместе за парту!</h3>
	        <p>Наступает осенняя пора, дети идут в школу, начинаются учебные будни. Вы можете вместе с питомцем тоже начать учиться</p>
	        <h3>Условия акции</h3>
	        <p>1. Делай покупки, получай марки: 1 марка  = <?= StampService::MARK_RATE ?> руб.;</p>
	        <p>2. Отслеживай марки где удобно:<br>
		        на чеке, в личном кабинете на сайте и в приложении;</p>
	        <p>3. Выбери игру и добавь в корзину, нажми "списать марки";</p>
	        <p>4. Получи игру со скидкой и развивай питомца!</p>
	        <a href="/iqgames/">Подробные условия акции</a>
        </div>
    </div>

    <hr class="b-hr" />

    <?php
    $APPLICATION->IncludeComponent('articul:catalog.section.slider', 'stamps', [
        'SECTION_CODE' => 'stamps'
    ]);
    ?>

</div>
