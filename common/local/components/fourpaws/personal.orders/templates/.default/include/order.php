<?php /** @var Order $order */

use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderItem;

?>
<li class="b-accordion-order-item js-permutation-li js-item-content">
    <div class="b-accordion-order-item__visible js-premutation-accordion-content">
        <div class="b-accordion-order-item__info">
            <a class="b-accordion-order-item__open-accordion js-open-accordion"
               href="javascript:void(0);"
               title="">
                <span class="b-accordion-order-item__arrow">
                    <span class="b-icon b-icon--account">
                        <?= new SvgDecorator('icon-arrow-account', 25, 25) ?>
                    </span>
                </span>
                <span class="b-accordion-order-item__number-order">№ <?= $order->getId() ?>
                    от <?= $order->getFormatedDateInsert() ?></span>
            </a>
            <?php $countItems = $order->getItems()->count(); ?>
            <div class="b-accordion-order-item__info-order"><?= $countItems ?> <?= WordHelper::declension($countItems,
                    [
                        'товар',
                        'товара',
                        'товаров',
                    ]) ?> <?= $order->getAllWeight() > 0 ? '(' . $order->getFormatedAllWeight() . ' кг)' : ''; ?>
            </div>
        </div>
        <div class="b-accordion-order-item__adress">
            <div class="b-accordion-order-item__date b-accordion-order-item__date--new">
                <?= $order->getStatus() ?>
                <span><?= $order->getStatus() !== 'Y' ? 'с ' : '' ?><?= $order->getFormatedDateStatus() ?></span>
            </div>
            <div class="b-accordion-order-item__date b-accordion-order-item__date--pickup">
                <?= $order->getDelivery()->getDeliveryName() ?>
                <span><?= $order->getDateDelivery() ?></span>
            </div>
            <div class="b-adress-info b-adress-info--order">
                <?php if (!empty($order->getStore()->getMetro())) { ?>
                    <span class="b-adress-info__label b-adress-info__label--<?= $arResult['METRO']->get($order->getStore()->getMetro())['BRANCH']['UF_CLASS'] ?>"></span>
                    м. <?= $arResult['METRO']->get($order->getStore()->getMetro())['UF_NAME'] ?>,
                <?php } ?>
                <?= $order->getStore()->getAddress() ?>
                <?php if (!empty($order->getStore()->getScheduleString())) { ?>
                    <p class="b-adress-info__mode-operation"><?= $order->getStore()->getScheduleString() ?></p>
                <?php } ?>
            </div>
        </div>
        <div class="b-accordion-order-item__pay">
            <div class="b-accordion-order-item__not-pay">
                <?= $order->getPayPrefixText() . ' ' . $order->getPayment()->getName() ?>
            </div>
        </div>
        <div class="b-accordion-order-item__button js-button-default">
            <?php if ($order->isClosed() && !$order->isManzana()) {
                $uri = new Uri(Application::getInstance()->getContext()->getRequest()->getRequestUri());
                $uri->addParams(['reply_order' => 'Y', 'id' => $order->getId()]); ?>
                <div class="b-accordion-order-item__subscribe-link b-accordion-order-item__subscribe-link--full">
                    <a class="b-link b-link--repeat-order b-link--repeat-order" href="<?= $uri->getUri() ?>"
                       title="Повторить заказ">
                        <span class="b-link__text b-link__text--repeat-order">Повторить заказ</span>
                    </a>
                </div>
            <?php } ?>
            <?php if (!$order->isClosed() && !$order->isPayed() && !$order->isManzana() && $order->getPayment()->getCode() === 'card-online') { ?>
                <div class="b-accordion-order-item__subscribe-link b-accordion-order-item__subscribe-link--full">
                    <a class="b-link b-link--pay-account b-link--pay-account"
                       href="/sale/payment/?ORDER_ID=<?= $order->getId() ?>"
                       title="Оплатить">
                        <span class="b-link__text b-link__text--pay-account">Оплатить</span>
                    </a>
                </div>
            <?php } ?>
            <div class="b-accordion-order-item__sum b-accordion-order-item__sum--full"><?= $order->getFormatedPrice() ?>
                <span
                        class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
            </div>
            <?php /** @todo подписаться на доставку */ ?>
            <a class="b-accordion-order-item__subscribe js-open-popup" href="javascript:void(0);"
               title="Подписаться на доставку" data-popup-id="subscribe-delivery">Подписаться
                на&nbsp;доставку</a>
        </div>
    </div>
    <div class="b-accordion-order-item__hidden js-hidden-order">
        <ul class="b-list-order">
            <?php /** @var OrderItem $item */
            foreach ($order->getItems() as $item) { ?>
                <li class="b-list-order__item">
                    <div class="b-list-order__image-wrapper">
                        <img class="b-list-order__image js-image-wrapper"
                             src="<?= $item->getImagePath() ?>" alt="<?= $item->getName() ?>"
                             title="<?= $item->getName() ?>"
                             role="presentation"/>
                    </div>
                    <div class="b-list-order__wrapper">
                        <div class="b-list-order__info">
                            <?php /** @todo акционный товар */ ?>
                            <?php if ($item->isHaveStock()) { ?>
                                <div class="b-list-order__action">Сейчас
                                    участвует в акции
                                </div>
                            <?php } ?>
                            <div class="b-clipped-text b-clipped-text--account">
                                <span>
                                    <?php if (!empty($item->getBrand())) { ?>
                                        <strong><?= $item->getBrand() ?>  </strong>
                                    <?php } ?><?= $item->getName() ?>
                                </span>
                            </div>
                            <div class="b-list-order__option">
                                <?php if (!empty($item->getFlavour())) { ?>
                                    <div class="b-list-order__option-text">
                                        Вкус:
                                        <span><?= $item->getFlavour() ?></span>
                                    </div>
                                <?php } ?>
                                <?php if (!empty($item->getOfferSelectedProp())) { ?>
                                    <div class="b-list-order__option-text">
                                        <?= $item->getOfferSelectedPropName() ?>:
                                        <span><?= $item->getOfferSelectedProp() ?></span>
                                    </div>
                                <?php } ?>
                                <?php if ($item->getWeight() > 0) { ?>
                                    <div class="b-list-order__option-text">Вес:
                                        <span><?= round($item->getWeight() / 1000, 2) ?> кг</span>
                                    </div>
                                <?php } ?>
                                <?php if (!empty($item->getArticle())) { ?>
                                    <div class="b-list-order__option-text">Артикул:
                                        <span><?= $item->getArticle() ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="b-list-order__price">
                            <div class="b-list-order__sum b-list-order__sum--item"><?= $item->getFormatedSum() ?>
                                <span
                                        class="b-ruble b-ruble--account-accordion">&nbsp;₽</span>
                            </div>
                            <?php if ($item->getQuantity() > 1) { ?>
                                <div class="b-list-order__calculation"><?= $item->getFormatedPrice() ?> ₽
                                    × <?= $item->getQuantity() ?> шт
                                </div>
                            <?php } ?>
                            <?php if ($item->getBonus() > 0) { ?>
                                <div class="b-list-order__bonus">
                                    + <?= $item->getBonus() . ' ' . WordHelper::declension($item->getBonus(),
                                        ['бонус', 'бонуса', 'бонусов']) ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </li>
            <?php } ?>
        </ul>
        <div class="b-accordion-order-item__calculation-full">
            <ul class="b-characteristics-tab__list">
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account">
                        <span>Товары</span>
                        <div class="b-characteristics-tab__dots">
                        </div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">
                        <?= $order->getFormattedItemsSum() ?><span
                                class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
                <?php if ($order->getDelivery()->getPriceDelivery() > 0) { ?>
                    <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                        <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account">
                            <span>Доставка</span>
                            <div class="b-characteristics-tab__dots">
                            </div>
                        </div>
                        <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account">
                            <?= $order->getDelivery()->getFormatedPriceDelivery() ?><span
                                    class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                        </div>
                    </li>
                <?php } ?>
                <li class="b-characteristics-tab__item b-characteristics-tab__item--account">
                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--account b-characteristics-tab__characteristics-text--last">
                        <span>Итого к оплате</span>
                        <div class="b-characteristics-tab__dots">
                        </div>
                    </div>
                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--account b-characteristics-tab__characteristics-value--last">
                        <?= $order->getFormatedPrice() ?><span
                                class="b-ruble b-ruble--calculation-account">&nbsp;₽</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="b-accordion-order-item__mobile-bottom js-button-permutation-mobile">
    </div>
</li>