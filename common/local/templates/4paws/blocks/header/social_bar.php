<?php use FourPaws\Decorators\SvgDecorator; ?>

<div class="b-social-bar js-social-bar">
    <ul class="b-social-bar__list">
        <li class="b-social-bar__item">
            <a class="b-social-bar__link" href="<?= tplvar('social_link_vk') ?>" title="ВКонтакте" target="_blank">
                <span class="b-social-bar__icon b-social-bar__icon--default">
                    <?= new SvgDecorator('icon-vk-circle', 35, 35) ?>
                </span>
                <span class="b-social-bar__icon b-social-bar__icon--hover">
                    <?= new SvgDecorator('icon-vk-circle-fill', 35, 35) ?>
                </span>
            </a>
            <?= tplinvis('social_link_vk') ?>
        </li>
        <li class="b-social-bar__item">
            <a class="b-social-bar__link" href="<?= tplvar('social_link_fb') ?>" title="Facebook" target="_blank">
                <span class="b-social-bar__icon b-social-bar__icon--default">
                    <?= new SvgDecorator('icon-fb-circle', 35, 35) ?>
                </span>
                <span class="b-social-bar__icon b-social-bar__icon--hover">
                    <?= new SvgDecorator('icon-fb-circle-fill', 35, 35) ?>
                </span>
            </a>
            <?= tplinvis('social_link_fb') ?>
        </li>
        <li class="b-social-bar__item">
            <a class="b-social-bar__link" href="<?= tplvar('social_link_ok') ?>" title="Одноклассники" target="_blank">
                <span class="b-social-bar__icon b-social-bar__icon--default">
                    <?= new SvgDecorator('icon-ok-circle', 35, 35) ?>
                </span>
                <span class="b-social-bar__icon b-social-bar__icon--hover">
                    <?= new SvgDecorator('icon-ok-circle-fill', 35, 35) ?>
                </span>
            </a>
            <?= tplinvis('social_link_ok') ?>
        </li>
        <li class="b-social-bar__item">
            <a class="b-social-bar__link" href="<?= tplvar('social_link_youtube') ?>" title="Youtube" target="_blank">
                <span class="b-social-bar__icon b-social-bar__icon--default">
                    <?= new SvgDecorator('icon-youtube-circle', 35, 35) ?>
                </span>
                <span class="b-social-bar__icon b-social-bar__icon--hover">
                    <?= new SvgDecorator('icon-youtube-circle-fill', 35, 35) ?>
                </span>
            </a>
            <?= tplinvis('social_link_in') ?>
        </li>
        <li class="b-social-bar__item">
            <a class="b-social-bar__link" href="<?= tplvar('social_link_inst') ?>" title="Instagram" target="_blank">
                <span class="b-social-bar__icon b-social-bar__icon--default">
                    <?= new SvgDecorator('icon-inst-circle', 35, 35) ?>
                </span>
                <span class="b-social-bar__icon b-social-bar__icon--hover">
                    <?= new SvgDecorator('icon-inst-circle-fill', 35, 35) ?>
                </span>
            </a>
            <?= tplinvis('social_link_inst') ?>
        </li>
    </ul>
</div>
