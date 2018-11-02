<?php use FourPaws\Decorators\SvgDecorator; ?>

<div class="b-social-bar js-social-bar">
    <ul class="b-social-bar__list">
        <li class="b-social-bar__item">
            <a class="b-social-bar__link" href="http://vk.com/4lapy_ru" title="ВКонтакте" target="_blank">
                <span class="b-social-bar__icon b-social-bar__icon--default">
                    <?= new SvgDecorator('icon-vk-circle', 35, 35) ?>
                </span>
                <span class="b-social-bar__icon b-social-bar__icon--hover">
                    <?= new SvgDecorator('icon-vk-circle-fill', 35, 35) ?>
                </span>
            </a>
        </li>
        <li class="b-social-bar__item">
            <a class="b-social-bar__link" href="https://www.facebook.com/4laps" title="Facebook" target="_blank">
                <span class="b-social-bar__icon b-social-bar__icon--default">
                    <?= new SvgDecorator('icon-fb-circle', 35, 35) ?>
                </span>
                <span class="b-social-bar__icon b-social-bar__icon--hover">
                    <?= new SvgDecorator('icon-fb-circle-fill', 35, 35) ?>
                </span>
            </a>
        </li>
        <li class="b-social-bar__item">
            <a class="b-social-bar__link" href="https://ok.ru/chetyre.lapy" title="Одноклассники" target="_blank">
                <span class="b-social-bar__icon b-social-bar__icon--default">
                    <?= new SvgDecorator('icon-ok-circle', 35, 35) ?>
                </span>
                <span class="b-social-bar__icon b-social-bar__icon--hover">
                    <?= new SvgDecorator('icon-ok-circle-fill', 35, 35) ?>
                </span>
            </a>
        </li>
        <li class="b-social-bar__item">
            <a class="b-social-bar__link" href="https://www.youtube.com/channel/UCduvxcmOQFwTewukh9DUpvQ" title="Youtube" target="_blank">
                <span class="b-social-bar__icon b-social-bar__icon--default">
                    <?= new SvgDecorator('icon-youtube-circle', 35, 35) ?>
                </span>
                <span class="b-social-bar__icon b-social-bar__icon--hover">
                    <?= new SvgDecorator('icon-youtube-circle-fill', 35, 35) ?>
                </span>
            </a>
        </li>
        <li class="b-social-bar__item">
            <a class="b-social-bar__link" href="https://www.instagram.com/4lapy.ru/" title="Instagram" target="_blank">
                <span class="b-social-bar__icon b-social-bar__icon--default">
                    <?= new SvgDecorator('icon-inst-circle', 35, 35) ?>
                </span>
                <span class="b-social-bar__icon b-social-bar__icon--hover">
                    <?= new SvgDecorator('icon-inst-circle-fill', 35, 35) ?>
                </span>
            </a>
        </li>
    </ul>
</div>
