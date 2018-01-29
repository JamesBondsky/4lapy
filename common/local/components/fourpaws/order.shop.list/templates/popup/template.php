<?php

?>
<section class="b-popup-wrapper__wrapper-modal b-popup-wrapper__wrapper-modal--order js-popup-section"
         data-popup="popup-order-stores">
    <section class="b-popup-pick-city b-popup-pick-city--order-stores js-popup-section" data-popup="popup-order-stores">
        <a class="b-popup-pick-city__close b-popup-pick-city__close--order js-close-popup"
           href="javascript:void(0);"
           title="Закрыть"></a>
        <div class="b-availability b-availability--order">
            <div class="b-availability__content b-availability__content--order js-availability-content">
                <div class="b-availability__info-block">
                    <a class="b-link b-link--popup-back b-link--popup-choose-shop js-close-popup"
                       href="javascript:void(0);">Выберите пункт самовывоза</a>
                    <h4 class="b-availability__header b-availability__header--desktop">Наши
                        магазины<span class="b-availability__header-amount">(всего 32)</span>
                    </h4>
                    <h4 class="b-availability__header b-availability__header--tablet active">Выберите пункт самовывоза
                    </h4>
                    <h4 class="b-availability__header b-availability__header--tablet b-availability__header--popuped">
                        Пункт самовывоза
                    </h4>
                    <ul class="b-availability-tab-list b-availability-tab-list--order js-availability-list">
                        <li class="b-availability-tab-list__item active">
                            <a class="b-availability-tab-list__link js-product-list"
                               href="javascript:void(0)"
                               aria-controls="shipping-list"
                               title="Списком">Списком</a>
                        </li>
                        <li class="b-availability-tab-list__item">
                            <a class="b-availability-tab-list__link js-product-map"
                               href="javascript:void(0)"
                               aria-controls="on-map"
                               title="На карте">На карте</a>
                        </li>
                    </ul>
                    <div class="b-stores-sort b-stores-sort--order b-stores-sort--balloon">
                        <div class="b-stores-sort__checkbox-block b-stores-sort__checkbox-block--balloon">
                            <div class="b-checkbox b-checkbox--stores b-checkbox--order">
                                <input class="b-checkbox__input"
                                       type="checkbox"
                                       name="stores-sort-time"
                                       id="stores-sort-1"/>
                                <label class="b-checkbox__name b-checkbox__name--stores b-checkbox__name--order"
                                       for="stores-sort-1"><span class="b-checkbox__text">работают <span class="b-checkbox__text-desktop">круглосуточно</span><span
                                                class="b-checkbox__text-mobile">24 часа</span></span>
                                </label>
                            </div>
                            <div class="b-checkbox b-checkbox--stores b-checkbox--order">
                                <input class="b-checkbox__input"
                                       type="checkbox"
                                       name="stores-sort-avlbl"
                                       id="stores-sort-2"
                                       value="в наличии сегодня"/>
                                <label class="b-checkbox__name b-checkbox__name--stores b-checkbox__name--order"
                                       for="stores-sort-2"><span class="b-checkbox__text">в наличии сегодня</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-inline b-form-inline--order-search">
                        <form class="b-form-inline__form">
                            <div class="b-input b-input--stores-search b-input--order-search">
                                <input class="b-input__input-field b-input__input-field--stores-search b-input__input-field--order-search"
                                       type="text"
                                       id="stores-search"
                                       placeholder="Поиск по адресу, метро и названию ТЦ"
                                       name="text"
                                       data-url="json/mapobjects-stores.json"/>
                                <div class="b-error"><span class="js-message"></span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="b-tab-delivery b-tab-delivery--order js-content-list js-map-list-scroll">
                        <ul class="b-delivery-list b-delivery-list--order js-delivery-list">
                            <li class="b-delivery-list__item"><a class="b-delivery-list__link js-shop-link b-active"
                                                                 id="shop_id1"
                                                                 data-shop-id="{{id}}"
                                                                 href="javascript:void(0);"
                                                                 title=""><span class="b-delivery-list__col b-delivery-list__col--addr"><span
                                                class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--blue"></span> {{adress}}</span><span
                                            class="b-delivery-list__col b-delivery-list__col--time">{{schedule}}</span><span
                                            class="b-delivery-list__col b-delivery-list__col--self-picked">{{pickup}}</span></a>
                                <div class="b-order-info-baloon">
                                    <a class="b-link b-link--popup-back b-link--order b-link--desktop js-close-order-baloon"
                                       href="javascript:void(0);"
                                       title=""> <span class="b-icon b-icon--back-long b-icon--balloon">
                        <svg class="b-icon__svg" viewBox="0 0 13 11 " width="13px" height="11px">
                          <use class="b-icon__use"
                               xlink:href="icons.svg#icon-back-form"
                               href="icons.svg#icon-back-form">
                          </use>
                        </svg></span>Вернуться к
                                        списку</a><a class="b-link b-link--popup-back b-link--baloon js-close-order-baloon"
                                                     href="javascript:void(0);">Пункт самовывоза</a>
                                    <div class="b-order-info-baloon__content js-order-info-baloon-scroll">
                                        <ul class="b-delivery-list">
                                            <li class="b-delivery-list__item b-delivery-list__item--myself">
                                                <span class="b-delivery-list__link b-delivery-list__link--myself"><span
                                                            class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--blue"></span>м. Улица Академика Янгеля, ул. Чертановская, д. 63/2, Москва</span>
                                            </li>
                                        </ul>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Время работы</span>
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">пн–пт:
                                                09:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">сб:
                                                10:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">вс:
                                                10:00–20:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Можно забрать через час, кроме</span>
                                                <ol class="b-input-line__text-list">
                                                    <li class="b-input-line__text-item">Moderna Миска пластиковая для
                                                        кошек 210 мл friends forever синяя
                                                    </li>
                                                    <li class="b-input-line__text-item">Ламистер Mealfell
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Полный заказ будет доступен</span>
                                            </div>
                                            <div class="b-input-line__text-line">05.09 (среда) с 15:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Оплата в магазине</span>
                                            </div>
                                            <div class="b-input-line__text-line"><span class="b-input-line__pay-type"><span
                                                            class="b-icon b-icon--icon-cash">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-cash" href="icons.svg#icon-cash">
                                </use>
                              </svg></span>наличными</span><span class="b-input-line__pay-type"><span class="b-icon b-icon--icon-bank">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use"
                                     xlink:href="icons.svg#icon-bank-card"
                                     href="icons.svg#icon-bank-card">
                                </use>
                              </svg></span>банковской картой</span>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--pin">
                                            <a class="b-link b-link--pin js-shop-link"
                                               href="javascript:void(0);"
                                               title=""> <span class="b-icon b-icon--pin">
                            <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-geo" href="icons.svg#icon-geo">
                              </use>
                            </svg></span>Показать на карте</a>
                                        </div>
                                        <a class="b-button b-button--order-balloon js-shop-myself"
                                           href="javascript:void(0);"
                                           title=""
                                           data-shopId="00001254"
                                           data-url="json/mapobjects-order-shop.json">Выбрать этот пункт самовывоза</a>
                                    </div>
                                </div>
                            </li>
                            <li class="b-delivery-list__item"><a class="b-delivery-list__link js-shop-link"
                                                                 id="shop_id2"
                                                                 data-shop-id="2"
                                                                 href="javascript:void(0);"
                                                                 title=""><span class="b-delivery-list__col b-delivery-list__col--addr"><span
                                                class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--green"></span> м. Автозаводская, ул. Мастеркова, д. 1, Москва</span><span
                                            class="b-delivery-list__col b-delivery-list__col--time">10:00—21:00</span><span
                                            class="b-delivery-list__col b-delivery-list__col--self-picked">заказ можно забрать через час</span></a>
                                <div class="b-order-info-baloon">
                                    <a class="b-link b-link--popup-back b-link--order b-link--desktop js-close-order-baloon"
                                       href="javascript:void(0);"
                                       title=""> <span class="b-icon b-icon--back-long b-icon--balloon">
                        <svg class="b-icon__svg" viewBox="0 0 13 11 " width="13px" height="11px">
                          <use class="b-icon__use"
                               xlink:href="icons.svg#icon-back-form"
                               href="icons.svg#icon-back-form">
                          </use>
                        </svg></span>Вернуться к
                                        списку</a><a class="b-link b-link--popup-back b-link--baloon js-close-order-baloon"
                                                     href="javascript:void(0);">Пункт самовывоза</a>
                                    <div class="b-order-info-baloon__content js-order-info-baloon-scroll">
                                        <ul class="b-delivery-list">
                                            <li class="b-delivery-list__item b-delivery-list__item--myself">
                                                <span class="b-delivery-list__link b-delivery-list__link--myself"><span
                                                            class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--blue"></span>м. Улица Академика Янгеля, ул. Чертановская, д. 63/2, Москва</span>
                                            </li>
                                        </ul>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Время работы</span>
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">пн–пт:
                                                09:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">сб:
                                                10:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">вс:
                                                10:00–20:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Можно забрать через час, кроме</span>
                                                <ol class="b-input-line__text-list">
                                                    <li class="b-input-line__text-item">Moderna Миска пластиковая для
                                                        кошек 210 мл friends forever синяя
                                                    </li>
                                                    <li class="b-input-line__text-item">Ламистер Mealfell
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Полный заказ будет доступен</span>
                                            </div>
                                            <div class="b-input-line__text-line">05.09 (среда) с 15:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Оплата в магазине</span>
                                            </div>
                                            <div class="b-input-line__text-line"><span class="b-input-line__pay-type"><span
                                                            class="b-icon b-icon--icon-cash">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-cash" href="icons.svg#icon-cash">
                                </use>
                              </svg></span>наличными</span><span class="b-input-line__pay-type"><span class="b-icon b-icon--icon-bank">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use"
                                     xlink:href="icons.svg#icon-bank-card"
                                     href="icons.svg#icon-bank-card">
                                </use>
                              </svg></span>банковской картой</span>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--pin">
                                            <a class="b-link b-link--pin js-shop-link"
                                               href="javascript:void(0);"
                                               title=""> <span class="b-icon b-icon--pin">
                            <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-geo" href="icons.svg#icon-geo">
                              </use>
                            </svg></span>Показать на карте</a>
                                        </div>
                                        <a class="b-button b-button--order-balloon js-shop-myself"
                                           href="javascript:void(0);"
                                           title=""
                                           data-shopId="00001254"
                                           data-url="json/mapobjects-order-shop.json">Выбрать этот пункт самовывоза</a>
                                    </div>
                                </div>
                            </li>
                            <li class="b-delivery-list__item"><a class="b-delivery-list__link js-shop-link"
                                                                 id="shop_id3"
                                                                 data-shop-id="3"
                                                                 href="javascript:void(0);"
                                                                 title=""><span class="b-delivery-list__col b-delivery-list__col--addr"><span
                                                class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--green"></span> м. Алма-атинская, Борисовские пруды, д. 26, Москва, ТЦ «Ключевой»</span><span
                                            class="b-delivery-list__col b-delivery-list__col--time">10:00—21:00</span><span
                                            class="b-delivery-list__col b-delivery-list__col--self-picked">заказ можно забрать через час</span></a>
                                <div class="b-order-info-baloon">
                                    <a class="b-link b-link--popup-back b-link--order b-link--desktop js-close-order-baloon"
                                       href="javascript:void(0);"
                                       title=""> <span class="b-icon b-icon--back-long b-icon--balloon">
                        <svg class="b-icon__svg" viewBox="0 0 13 11 " width="13px" height="11px">
                          <use class="b-icon__use"
                               xlink:href="icons.svg#icon-back-form"
                               href="icons.svg#icon-back-form">
                          </use>
                        </svg></span>Вернуться к
                                        списку</a><a class="b-link b-link--popup-back b-link--baloon js-close-order-baloon"
                                                     href="javascript:void(0);">Пункт самовывоза</a>
                                    <div class="b-order-info-baloon__content js-order-info-baloon-scroll">
                                        <ul class="b-delivery-list">
                                            <li class="b-delivery-list__item b-delivery-list__item--myself">
                                                <span class="b-delivery-list__link b-delivery-list__link--myself"><span
                                                            class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--blue"></span>м. Улица Академика Янгеля, ул. Чертановская, д. 63/2, Москва</span>
                                            </li>
                                        </ul>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Время работы</span>
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">пн–пт:
                                                09:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">сб:
                                                10:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">вс:
                                                10:00–20:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Можно забрать через час, кроме</span>
                                                <ol class="b-input-line__text-list">
                                                    <li class="b-input-line__text-item">Moderna Миска пластиковая для
                                                        кошек 210 мл friends forever синяя
                                                    </li>
                                                    <li class="b-input-line__text-item">Ламистер Mealfell
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Полный заказ будет доступен</span>
                                            </div>
                                            <div class="b-input-line__text-line">05.09 (среда) с 15:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Оплата в магазине</span>
                                            </div>
                                            <div class="b-input-line__text-line"><span class="b-input-line__pay-type"><span
                                                            class="b-icon b-icon--icon-cash">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-cash" href="icons.svg#icon-cash">
                                </use>
                              </svg></span>наличными</span><span class="b-input-line__pay-type"><span class="b-icon b-icon--icon-bank">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use"
                                     xlink:href="icons.svg#icon-bank-card"
                                     href="icons.svg#icon-bank-card">
                                </use>
                              </svg></span>банковской картой</span>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--pin">
                                            <a class="b-link b-link--pin js-shop-link"
                                               href="javascript:void(0);"
                                               title=""> <span class="b-icon b-icon--pin">
                            <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-geo" href="icons.svg#icon-geo">
                              </use>
                            </svg></span>Показать на карте</a>
                                        </div>
                                        <a class="b-button b-button--order-balloon js-shop-myself"
                                           href="javascript:void(0);"
                                           title=""
                                           data-shopId="00001254"
                                           data-url="json/mapobjects-order-shop.json">Выбрать этот пункт самовывоза</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <h4 class="b-tab-delivery__addition-header">Заказ в наличии частично
                        </h4>
                        <ul class="b-delivery-list b-delivery-list--order js-delivery-part-list">
                            <li class="b-delivery-list__item"><a class="b-delivery-list__link js-shop-link"
                                                                 id="shop_id4"
                                                                 data-shop-id="{{id}}"
                                                                 href="javascript:void(0);"
                                                                 title=""><span class="b-delivery-list__col b-delivery-list__col--addr"><span
                                                class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--green-light"></span> {{adress}}</span><span
                                            class="b-delivery-list__col b-delivery-list__col--time">{{schedule}}</span><span
                                            class="b-delivery-list__col b-delivery-list__col--self-picked">{{pickup}}</span><span
                                            class="b-delivery-list__col b-delivery-list__col--added">
                      <p>Сейчас нет в наличии:</p>
                      <ol>{{parts}}</ol></span></a>
                                <div class="b-order-info-baloon">
                                    <a class="b-link b-link--popup-back b-link--order b-link--desktop js-close-order-baloon"
                                       href="javascript:void(0);"
                                       title=""> <span class="b-icon b-icon--back-long b-icon--balloon">
                        <svg class="b-icon__svg" viewBox="0 0 13 11 " width="13px" height="11px">
                          <use class="b-icon__use"
                               xlink:href="icons.svg#icon-back-form"
                               href="icons.svg#icon-back-form">
                          </use>
                        </svg></span>Вернуться к
                                        списку</a><a class="b-link b-link--popup-back b-link--baloon js-close-order-baloon"
                                                     href="javascript:void(0);">Пункт самовывоза</a>
                                    <div class="b-order-info-baloon__content js-order-info-baloon-scroll">
                                        <ul class="b-delivery-list">
                                            <li class="b-delivery-list__item b-delivery-list__item--myself">
                                                <span class="b-delivery-list__link b-delivery-list__link--myself"><span
                                                            class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--blue"></span>м. Улица Академика Янгеля, ул. Чертановская, д. 63/2, Москва</span>
                                            </li>
                                        </ul>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Время работы</span>
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">пн–пт:
                                                09:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">сб:
                                                10:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">вс:
                                                10:00–20:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Можно забрать через час, кроме</span>
                                                <ol class="b-input-line__text-list">
                                                    <li class="b-input-line__text-item">Moderna Миска пластиковая для
                                                        кошек 210 мл friends forever синяя
                                                    </li>
                                                    <li class="b-input-line__text-item">Ламистер Mealfell
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Полный заказ будет доступен</span>
                                            </div>
                                            <div class="b-input-line__text-line">05.09 (среда) с 15:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Оплата в магазине</span>
                                            </div>
                                            <div class="b-input-line__text-line"><span class="b-input-line__pay-type"><span
                                                            class="b-icon b-icon--icon-cash">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-cash" href="icons.svg#icon-cash">
                                </use>
                              </svg></span>наличными</span><span class="b-input-line__pay-type"><span class="b-icon b-icon--icon-bank">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use"
                                     xlink:href="icons.svg#icon-bank-card"
                                     href="icons.svg#icon-bank-card">
                                </use>
                              </svg></span>банковской картой</span>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--pin">
                                            <a class="b-link b-link--pin js-shop-link"
                                               href="javascript:void(0);"
                                               title=""> <span class="b-icon b-icon--pin">
                            <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-geo" href="icons.svg#icon-geo">
                              </use>
                            </svg></span>Показать на карте</a>
                                        </div>
                                        <a class="b-button b-button--order-balloon js-shop-myself"
                                           href="javascript:void(0);"
                                           title=""
                                           data-shopId="00001254"
                                           data-url="json/mapobjects-order-shop.json">Выбрать этот пункт самовывоза</a>
                                    </div>
                                </div>
                            </li>
                            <li class="b-delivery-list__item"><a class="b-delivery-list__link js-shop-link"
                                                                 id="shop_id5"
                                                                 data-shop-id="5"
                                                                 href="javascript:void(0);"
                                                                 title=""><span class="b-delivery-list__col b-delivery-list__col--addr"><span
                                                class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--purple"></span> м. Выхино, ул. Ташкентская, д. 2, Москва</span><span
                                            class="b-delivery-list__col b-delivery-list__col--time">10:00—21:00</span><span
                                            class="b-delivery-list__col b-delivery-list__col--self-picked">полный заказ будет доступен 05.09 (ср) с 15:00</span><span
                                            class="b-delivery-list__col b-delivery-list__col--added">
                      <p>Сейчас нет в наличии:</p>
                      <ol>
                        <li>Moderna Миска двойная пластиковая для кош…</li>
                        <li>Mealfeel консервы для кошек с домашней птиц…</li>
                      </ol></span></a>
                                <div class="b-order-info-baloon">
                                    <a class="b-link b-link--popup-back b-link--order b-link--desktop js-close-order-baloon"
                                       href="javascript:void(0);"
                                       title=""> <span class="b-icon b-icon--back-long b-icon--balloon">
                        <svg class="b-icon__svg" viewBox="0 0 13 11 " width="13px" height="11px">
                          <use class="b-icon__use"
                               xlink:href="icons.svg#icon-back-form"
                               href="icons.svg#icon-back-form">
                          </use>
                        </svg></span>Вернуться к
                                        списку</a><a class="b-link b-link--popup-back b-link--baloon js-close-order-baloon"
                                                     href="javascript:void(0);">Пункт самовывоза</a>
                                    <div class="b-order-info-baloon__content js-order-info-baloon-scroll">
                                        <ul class="b-delivery-list">
                                            <li class="b-delivery-list__item b-delivery-list__item--myself">
                                                <span class="b-delivery-list__link b-delivery-list__link--myself"><span
                                                            class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--blue"></span>м. Улица Академика Янгеля, ул. Чертановская, д. 63/2, Москва</span>
                                            </li>
                                        </ul>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Время работы</span>
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">пн–пт:
                                                09:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">сб:
                                                10:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">вс:
                                                10:00–20:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Можно забрать через час, кроме</span>
                                                <ol class="b-input-line__text-list">
                                                    <li class="b-input-line__text-item">Moderna Миска пластиковая для
                                                        кошек 210 мл friends forever синяя
                                                    </li>
                                                    <li class="b-input-line__text-item">Ламистер Mealfell
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Полный заказ будет доступен</span>
                                            </div>
                                            <div class="b-input-line__text-line">05.09 (среда) с 15:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Оплата в магазине</span>
                                            </div>
                                            <div class="b-input-line__text-line"><span class="b-input-line__pay-type"><span
                                                            class="b-icon b-icon--icon-cash">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-cash" href="icons.svg#icon-cash">
                                </use>
                              </svg></span>наличными</span><span class="b-input-line__pay-type"><span class="b-icon b-icon--icon-bank">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use"
                                     xlink:href="icons.svg#icon-bank-card"
                                     href="icons.svg#icon-bank-card">
                                </use>
                              </svg></span>банковской картой</span>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--pin">
                                            <a class="b-link b-link--pin js-shop-link"
                                               href="javascript:void(0);"
                                               title=""> <span class="b-icon b-icon--pin">
                            <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-geo" href="icons.svg#icon-geo">
                              </use>
                            </svg></span>Показать на карте</a>
                                        </div>
                                        <a class="b-button b-button--order-balloon js-shop-myself"
                                           href="javascript:void(0);"
                                           title=""
                                           data-shopId="00001254"
                                           data-url="json/mapobjects-order-shop.json">Выбрать этот пункт самовывоза</a>
                                    </div>
                                </div>
                            </li>
                            <li class="b-delivery-list__item"><a class="b-delivery-list__link js-shop-link"
                                                                 id="shop_id6"
                                                                 data-shop-id="6"
                                                                 href="javascript:void(0);"
                                                                 title=""><span class="b-delivery-list__col b-delivery-list__col--addr"><span
                                                class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--purple"></span> м. Выхино, мкр-н Жулебино, ул. Генерала Кузнецова, д. 13, Москва</span><span
                                            class="b-delivery-list__col b-delivery-list__col--time">10:00—21:00</span><span
                                            class="b-delivery-list__col b-delivery-list__col--self-picked">полный заказ будет доступен 05.09 (ср) с 15:00</span><span
                                            class="b-delivery-list__col b-delivery-list__col--added">
                      <p>Сейчас нет в наличии:</p>
                      <ol>
                        <li>Moderna Миска двойная пластиковая для кош…</li>
                        <li>Mealfeel консервы для кошек с домашней птиц…</li>
                      </ol></span></a>
                                <div class="b-order-info-baloon">
                                    <a class="b-link b-link--popup-back b-link--order b-link--desktop js-close-order-baloon"
                                       href="javascript:void(0);"
                                       title=""> <span class="b-icon b-icon--back-long b-icon--balloon">
                        <svg class="b-icon__svg" viewBox="0 0 13 11 " width="13px" height="11px">
                          <use class="b-icon__use"
                               xlink:href="icons.svg#icon-back-form"
                               href="icons.svg#icon-back-form">
                          </use>
                        </svg></span>Вернуться к
                                        списку</a><a class="b-link b-link--popup-back b-link--baloon js-close-order-baloon"
                                                     href="javascript:void(0);">Пункт самовывоза</a>
                                    <div class="b-order-info-baloon__content js-order-info-baloon-scroll">
                                        <ul class="b-delivery-list">
                                            <li class="b-delivery-list__item b-delivery-list__item--myself">
                                                <span class="b-delivery-list__link b-delivery-list__link--myself"><span
                                                            class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--blue"></span>м. Улица Академика Янгеля, ул. Чертановская, д. 63/2, Москва</span>
                                            </li>
                                        </ul>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Время работы</span>
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">пн–пт:
                                                09:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">сб:
                                                10:00–21:00
                                            </div>
                                            <div class="b-input-line__text-line b-input-line__text-line--myself">вс:
                                                10:00–20:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Можно забрать через час, кроме</span>
                                                <ol class="b-input-line__text-list">
                                                    <li class="b-input-line__text-item">Moderna Миска пластиковая для
                                                        кошек 210 мл friends forever синяя
                                                    </li>
                                                    <li class="b-input-line__text-item">Ламистер Mealfell
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Полный заказ будет доступен</span>
                                            </div>
                                            <div class="b-input-line__text-line">05.09 (среда) с 15:00
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--myself">
                                            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Оплата в магазине</span>
                                            </div>
                                            <div class="b-input-line__text-line"><span class="b-input-line__pay-type"><span
                                                            class="b-icon b-icon--icon-cash">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-cash" href="icons.svg#icon-cash">
                                </use>
                              </svg></span>наличными</span><span class="b-input-line__pay-type"><span class="b-icon b-icon--icon-bank">
                              <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                                <use class="b-icon__use"
                                     xlink:href="icons.svg#icon-bank-card"
                                     href="icons.svg#icon-bank-card">
                                </use>
                              </svg></span>банковской картой</span>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--pin">
                                            <a class="b-link b-link--pin js-shop-link"
                                               href="javascript:void(0);"
                                               title=""> <span class="b-icon b-icon--pin">
                            <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-geo" href="icons.svg#icon-geo">
                              </use>
                            </svg></span>Показать на карте</a>
                                        </div>
                                        <a class="b-button b-button--order-balloon js-shop-myself"
                                           href="javascript:void(0);"
                                           title=""
                                           data-shopId="00001254"
                                           data-url="json/mapobjects-order-shop.json">Выбрать этот пункт самовывоза</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="b-availability__show-block">
                    <div class="b-tab-delivery-map b-tab-delivery-map--order js-content-map">
                        <div class="b-tab-delivery-map__map" id="map" data-url="/ajax/store/list/order/">
                        </div>
                        <a class="b-link b-link--close-baloon js-product-list" href="javascript:void(0);" title=""><span
                                    class="b-icon b-icon--close-baloon">
                  <svg class="b-icon__svg" viewBox="0 0 18 18 " width="18px" height="18px">
                    <use class="b-icon__use"
                         xlink:href="icons.svg#icon-close-baloon"
                         href="icons.svg#icon-close-baloon">
                    </use>
                  </svg></span></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
