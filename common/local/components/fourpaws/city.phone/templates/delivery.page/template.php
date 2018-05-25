<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arParams
 * @var array $arResult
 * @var array $templateData
 *
 * @var string $componentPath
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 *
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

$this->setFrameMode(true);

?>
<div class="b-container b-container--delivery">
    <div class="b-delivery">
        <div class="b-delivery__return">
            <p class="b-title b-title--h2">Условия возврата</p>
            <div class="b-delivery__return-row">
                <p>В случае, если вдруг вы решите вернуть уже оплаченный товар, вы можете позвонить в наш контакт-центр
                    по телефону
                    <?php $frame = $this->createFrame()->begin($arResult['DEFAULT_PHONE']) ?>
                    <?= $arResult['PHONE'] ?>
                    <?php $frame->end() ?>
                </p>
                <p>Срок возврата товара надлежащего качества составляет 30 дней с момента получения товара.</p>
                <p>Возврат переведенных средств, производится на Ваш банковский счет в течение 5—30 рабочих дней (срок
                    зависит от Банка, который выдал Вашу банковскую карту).</p>
                <?php
                /** @see https://jira.adv.ru/browse/LP12-47
                <a href="/customer/return-policy/">Правила возврата и обмена</a>
                 */ ?>
            </div>
        </div>
    </div>
</div>
