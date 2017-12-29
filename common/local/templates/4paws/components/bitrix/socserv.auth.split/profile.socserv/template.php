<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use FourPaws\Decorators\SvgDecorator;

if (!empty($arResult['AUTH_SERVICES']) && \is_array($arResult['AUTH_SERVICES'])) {
    ?>
    <div class="b-account-profile__column b-account-profile__column--bottom">
        <div class="b-account-profile__title b-account-profile__title--small">
            Соцсети
        </div>
        <div class="b-account-profile__text">
            Вы можете привязать свой аккаунт соцсети, чтобы в дальнейшем входить без ввода пароля.
        </div>
        <?php if ($arResult['ERROR_MESSAGE']) {
        ShowMessage($arResult['ERROR_MESSAGE']);
    } ?>
        <div class="b-account-profile__social">
            <?php
            /** @noinspection ForeachSourceInspection */
            foreach ($arResult['AUTH_SERVICES'] as $service) {
                ?>
                <div class="b-account-social<?= $service['ACTIVE'] ? ' active' : '' ?>">
                    <a class="b-account-social__link"
                       href="javascript:void(0);" <?= $service['FORM_HTML']['ON_CLICK'] ?? '' ?>
                       title="<?= $service['SOCSERV_NAME'] ?>">
                            <span class="b-account-social__icon">
                                <span class="b-icon b-icon--account-social b-icon--account-<?= $service['ICON'] ?>">
                                    <?= new SvgDecorator(
                                        'icon-' . $service['ICON_DECORATOR']['CODE'],
                                        $service['ICON_DECORATOR']['WIDTH'],
                                        $service['ICON_DECORATOR']['HEIGHT']
                                    ) ?>
                                </span>
                            </span>
                        <span class="b-account-social__text">
                                <span class="b-account-social__old js-old-text">Привязать</span> <?= $service['SOCSERV_NAME'] ?>
                            <span class="b-account-social__new js-new-text">привязан</span>
                            </span>
                    </a>
                    <a class="b-account-social__close"
                        <?php if (is_numeric($service['ID'])) {
                                        ?>
                            href="<?= htmlspecialcharsbx($service['DELETE_LINK']) ?>"
                            onclick="return confirm('<?= Loc::getMessage('SS_PROFILE_DELETE_CONFIRM') ?>')"
                            <?php
                                    } ?>
                       title="close">
                        <span class="b-icon b-icon--account-social-del">
                            <?= new SvgDecorator(
                                'icon-delete',
                                13,
                                13
                            ) ?>
                        </span>
                    </a>
                </div>
                <?php
            } ?>
        </div>
    </div>
    <?php
}
