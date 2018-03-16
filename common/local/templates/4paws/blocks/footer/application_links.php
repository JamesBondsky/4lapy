<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<div class="b-footer__column b-footer__column--small js-here-app-permutantion">
    <div class="b-app js-app-permutation">
        <dl class="b-app__item b-app__item--app">
            <dt class="b-app__name">Наши приложения</dt>
            <dd class="b-app__block">
                <?php if (tplvar('application_ios')) { ?>
                    <a class="b-app__link b-app__link--app" href="<?= tplvar('application_ios') ?>" title="">
                        <img src="/static/build/images/inhtml/app-store.png" title="" alt="" role="presentation" />
                    </a>
                <?php } ?>
                <?= tplinvis('application_ios') ?>
                <?php if (tplvar('application_android')) { ?>
                    <a class="b-app__link b-app__link--app" href="<?= tplvar('application_android') ?>" title="Скоро">
                        <img src="/static/build/images/inhtml/android.png" title="" alt="" role="presentation" />
                    </a>
                <?php } ?>
                <?= tplinvis('application_android') ?>
            </dd>
        </dl>
        <dl class="b-app__item">
            <dt class="b-app__name">Наш рейтинг на Яндекс.Маркет</dt>
            <dd class="b-app__block">
                <?php if (tplvar('rating_yandex')) { ?>
                    <a class="b-app__link" href="<?= tplvar('rating_yandex') ?>" title="" target="_blank">
                        <img src="/static/build/images/inhtml/yandex.png" title="" alt="" role="presentation" />
                    </a>
                <?php } ?>
                <?= tplinvis('rating_yandex') ?>
            </dd>
        </dl>
        <dl class="b-app__item">
            <dt class="b-app__name">Способы оплаты</dt>
            <dd class="b-app__block">
                <a class="b-app__link b-app__link--payment" href="/customer/payment-and-delivery/" title="">
                    <img src="/static/build/images/inhtml/visa.png" title="" alt="" role="presentation" />
                </a>
                <a class="b-app__link b-app__link--payment" href="/customer/payment-and-delivery/" title="">
                    <img src="/static/build/images/inhtml/master-card.png" title="" alt="" role="presentation" />
                </a>
                <a class="b-app__link b-app__link--payment" href="/customer/payment-and-delivery/" title="">
                    <img src="/static/build/images/inhtml/mir.png" title="" alt="" role="presentation" />
                </a>
            </dd>
        </dl>
    </div>
</div>
