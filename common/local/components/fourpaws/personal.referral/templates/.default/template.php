<?php

use Bitrix\Main\UI\PageNavigation;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Referral;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain $APPLICATION
 */

/** @var ArrayCollection $items */
$items = $arResult['ITEMS'];
$noItems = $items->isEmpty();
$showTab = !$noItems || !empty($arResult['search']) || !empty($arResult['referral_type'])
?>
<div class="b-account-referal">
    <div class="b-account-referal-top">
        <div class="b-account-referal-top__info-block">
            <a class="b-link b-link--add-referal js-open-popup js-open-popup--add-referal"
               href="javascript:void(0)"
               title="Добавить реферала"
               data-popup-id="add-referal">
                <span class="b-link__text b-link__text--add-referal">Добавить реферала</span>
            </a>
            <div class="b-account-referal-top__text">Начисление баллов начнется после
                                                     успешной проверки данных
            </div>
        </div>
        <?php if ($showTab) { ?>
            <div class="b-account-referal-top__search">
                <div class="b-form-inline b-form-inline--search-referal">
                    <form class="b-form-inline__form b-form-inline__form--search-referal js-referal-search"
                          method="get"
                          action="<?= POST_FORM_ACTION_URI ?>">
                        <div class="b-input">
                            <input class="b-input__input-field"
                                   type="text"
                                   id="referal-search"
                                   placeholder="Найти реферала"
                                   name="search"
                            value="<?=$arResult['search']?>"/>
                        </div>
                        <button class="b-button b-button--form-inline b-button--search-referal" type="submit">
                            <span class="b-icon">
                                <?= new SvgDecorator('icon-search', 16, 16) ?>
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="b-account-referal__bottom">
        <?php if (!$noItems || $showTab) { ?>
            <div class="b-account-referal__bottom">
                <div class="b-account-referal__title">Список рефералов</div>
                <div class="b-account-referal__link-block">
                    <div class="b-account-referal__full-number">
                        <?php if (\is_array($arResult['TABS']) && !empty($arResult['TABS'])) { ?>
                            <div class="b-tab-title b-tab-title--referal">
                                <ul class="b-tab-title__list b-tab-title__list--referal">
                                    <?php foreach ($arResult['TABS'] as $code => $tab) { ?>
                                        <li class="b-tab-title__item <?= ($arResult['referral_type']
                                                                          === $code ? ' active' : '') ?> js-tab-referal-item">
                                            <a class="b-tab-title__link js-referal-link"
                                               href="<?= $tab['URI'] ?>"
                                               title="<?= $tab['NAME'] ?> ">
                                        <span class="b-tab-title__text">
                                            <?= $tab['NAME'] ?>
                                            <span class="b-tab-title__number">(<?= $tab['COUNT'] ?>)</span>
                                        </span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>
                        <div class="b-account-referal__text-number">Начислено за все время
                            <span><?= $arResult['FORMATED_BONUS'] ?></span>
                            <span class="b-ruble b-ruble--referal">&nbsp;₽</span>
                        </div>
                    </div>
                    <?php if(!$noItems){ ?>
                        <ul class="b-account-referal__list js-referal-list">
                            <?php /** @var Referral $item */
                            foreach ($items as $item) { ?>
                                <li class="b-account-referal-item js-item-referal"
                                    data-referal="<?= $item->isModerate() ? 'moderate' : 'active-referal' ?>">
                                    <div class="b-account-referal-item__wrapper">
                                        <div class="b-account-referal-item__column">
                                            <div class="b-account-referal-item__title"><?= $item->getFullName() ?></div>
                                            <div class="b-account-referal-item__info">
                                                <div class="b-account-referal-item__info-text b-account-referal-item__info-text--number">
                                                    <?= $item->getPhone() ?>
                                                </div>
                                                <div class="b-account-referal-item__info-text b-account-referal-item__info-text--email">
                                                    <?= $item->getEmail() ?>
                                                </div>
                                                <div class="b-account-referal-item__info-text b-account-referal-item__info-text--card">
                                                    <?= $item->getCard() ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="b-account-referal-item__column">
                                            <div class="b-account-referal-item__bonus">Начислено бонусов
                                                <span class="b-account-referal-item__number">
                                                    <span><?= WordHelper::numberFormat($item->getBonus()) ?></span>
                                                    <span class="b-ruble b-ruble--referal-item">&nbsp;₽</span></span>
                                            </div>
                                            <?php
                                            if ($item->isModerate()) { ?>
                                                <div class="b-account-referal-item__status b-account-referal-item__status--moderate">
                                                    На модерации
                                                </div>
                                                <?php
                                            }/** @todo показ даты активности карты */
                                            /*elseif($item->getDateEndActive() !== null && $item->getBonus() > 0) { ?>
                                                <div class="b-account-referal-item__status b-account-referal-item__status--<?= !$item->isEndActiveDate(
                                                ) ? 'active' : 'not-active' ?>">
                                                    <?= !$item->isEndActiveDate() ? 'Активна до ' : 'Неактивна c ' ?>
                                                    <span><?= $item->getFormatedActiveDate() ?></span>
                                                </div>
                                                <?php
                                            } */?>
                                        </div>
                                    </div>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    <?php } ?>
                </div>
                <?php if (!$noItems && $arResult['NAV'] instanceof PageNavigation) { ?>
                    <div class="b-pagination b-pagination--referal">
                        <?php
                        $APPLICATION->IncludeComponent(
                            'bitrix:main.pagenavigation',
                            'pagination',
                            [
                                'NAV_OBJECT' => $arResult['NAV'],
                                'SEF_MODE'   => 'N',
                                'AJAX_MODE'   => 'N',
                            ],
                            false
                        );
                        ?>
                    </div>
                <?php } ?>
            </div>
        <?php }
        if($noItems){?>
            <div class="b-account-referal__bottom">
                <div class="b-account-referal__text">
                    <p>У вас еще нет рефералов. Добавьте нового пользователя для получения
                       возможности зарабатывать баллы с его покупок.</p>
                    <p>Подробнее о реферальной программе вы можете узнать по телефону
                        <a class="b-account-referal__link-phone"
                           href="tel:<?= preg_replace('~[^+\d]~', '', tplvar('phone_main')) ?>"
                       title="позвони"><?= tplvar('phone_main') ?></a> или
                        <a class="b-account-referal__link" href="/company/partnership/"
                           title="сайт">на сайте.</a>
                    </p>
                </div>
            </div>
        <?php } ?>
    </div>
</div>