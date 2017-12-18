<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Мои питомцы');
?>
    
    <div class="b-account-adress">
        <div class="b-account-border-block b-account-border-block--pet">
            <div class="b-account-border-block__content b-account-border-block__content--pet">
                <div class="b-account-border-block__image-wrap">
                    <img class="b-account-border-block__image js-image-wrapper"
                         src="images/content/dog.jpg"
                         alt="Герда"
                         title="" />
                </div>
                <div class="b-account-border-block__info">
                    <div class="b-account-border-block__title b-account-border-block__title--pet">
                        Герда
                    </div>
                    <p class="b-account-border-block__pet">Карликовая такса</p>
                    <p class="b-account-border-block__pet">Девочка</p>
                    <p class="b-account-border-block__pet">2,5 года</p>
                </div>
            </div>
            <div class="b-account-border-block__button">
                <div class="b-account-border-block__wrapper-link">
                    <a class="b-account-border-block__link js-open-popup"
                       href="javascript:void(0);"
                       title="Редактировать"
                       data-popup-id="edit-popup-pet"><span class="b-icon b-icon--account-block"><svg
                                    class="b-icon__svg"
                                    viewBox="0 0 21 21 "
                                    width="21px"
                                    height="21px"><use class="b-icon__use"
                                                       xlink:href="icons.svg#icon-edit"></use></svg></span><span>Редактировать</span></a>
                </div>
                <div class="b-account-border-block__wrapper-link">
                    <a class="b-account-border-block__link js-del-popup-pet"
                       href="javascript:void(0);"
                       title="Удалить"><span class="b-icon b-icon--account-block"><svg class="b-icon__svg"
                                                                                       viewBox="0 0 21 21 "
                                                                                       width="21px"
                                                                                       height="21px"><use
                                        class="b-icon__use"
                                        xlink:href="icons.svg#icon-trash"></use></svg></span><span>Удалить</span></a>
                </div>
            </div>
            <div class="b-account-border-block__hidden js-hidden-del">
                <a class="b-account-border-block__link-delete js-close-hidden"
                   href="javascript:void(0);"
                   title="Удалить"><span class="b-icon b-icon--account-delete"><svg class="b-icon__svg"
                                                                                    viewBox="0 0 26 26 "
                                                                                    width="26px"
                                                                                    height="26px"><use
                                    class="b-icon__use"
                                    xlink:href="icons.svg#icon-delete"></use></svg></span></a>
                <div
                        class="b-account-border-block__title b-account-border-block__title--hidden">
                    Удалить
                    <p>4Лапы — Братиславская?</p>
                </div>
                <a class="b-link b-link--account-del b-link--account-del"
                   href="javascript:void(0)"
                   title="Удалить"><span class="b-link__text b-link__text--account-del">Удалить</span></a>
            </div>
        </div>
        <div class="b-account-border-block b-account-border-block--pet">
            <div class="b-account-border-block__content b-account-border-block__content--pet">
                <div class="b-account-border-block__image-wrap">
                    <img class="b-account-border-block__image js-image-wrapper"
                         src="images/content/cat.jpg"
                         alt="Дэймон Джозеф Мартин Лютер II"
                         title="" />
                </div>
                <div class="b-account-border-block__info">
                    <div class="b-account-border-block__title b-account-border-block__title--pet">
                        Дэймон Джозеф Мартин Лютер II
                    </div>
                    <p class="b-account-border-block__pet">Шотландский вислоухий</p>
                    <p class="b-account-border-block__pet">Мальчик</p>
                    <p class="b-account-border-block__pet">1 год</p>
                </div>
            </div>
            <div class="b-account-border-block__button">
                <div class="b-account-border-block__wrapper-link">
                    <a class="b-account-border-block__link js-open-popup"
                       href="javascript:void(0);"
                       title="Редактировать"
                       data-popup-id="edit-popup-pet"><span class="b-icon b-icon--account-block"><svg
                                    class="b-icon__svg"
                                    viewBox="0 0 21 21 "
                                    width="21px"
                                    height="21px"><use class="b-icon__use"
                                                       xlink:href="icons.svg#icon-edit"></use></svg></span><span>Редактировать</span></a>
                </div>
                <div class="b-account-border-block__wrapper-link">
                    <a class="b-account-border-block__link js-del-popup-pet"
                       href="javascript:void(0);"
                       title="Удалить"><span class="b-icon b-icon--account-block"><svg class="b-icon__svg"
                                                                                       viewBox="0 0 21 21 "
                                                                                       width="21px"
                                                                                       height="21px"><use
                                        class="b-icon__use"
                                        xlink:href="icons.svg#icon-trash"></use></svg></span><span>Удалить</span></a>
                </div>
            </div>
            <div class="b-account-border-block__hidden js-hidden-del">
                <a class="b-account-border-block__link-delete js-close-hidden"
                   href="javascript:void(0);"
                   title="Удалить"><span class="b-icon b-icon--account-delete"><svg class="b-icon__svg"
                                                                                    viewBox="0 0 26 26 "
                                                                                    width="26px"
                                                                                    height="26px"><use
                                    class="b-icon__use"
                                    xlink:href="icons.svg#icon-delete"></use></svg></span></a>
                <div
                        class="b-account-border-block__title b-account-border-block__title--hidden">
                    Удалить
                    <p>4Лапы — Братиславская?</p>
                </div>
                <a class="b-link b-link--account-del b-link--account-del"
                   href="javascript:void(0)"
                   title="Удалить"><span class="b-link__text b-link__text--account-del">Удалить</span></a>
            </div>
        </div>
        <div class="b-account-border-block b-account-border-block--dashed">
            <div class="b-account-border-block__content b-account-border-block__content--dashed">
                <div class="b-account-border-block__title b-account-border-block__title--dashed">
                    Зачем добавлять питомца?
                </div>
                <ul class="b-account-border-block__list">
                    <li class="b-account-border-block__item">Наиболее подходящие рекомендации в
                                                             интернет-магазине;
                    </li>
                    <li class="b-account-border-block__item">Полезные статьи по уходу вашего
                                                             питомца.
                    </li>
                </ul>
            </div>
            <div class="b-account-border-block__button">
                <a class="b-link b-link--account-tab js-open-popup js-open-popup--account-tab"
                   href="javascript:void(0)"
                   title="Добавить питомца"
                   data-popup-id="edit-popup-pet"><span class="b-link__text b-link__text--account-tab">Добавить питомца</span></a>
            </div>
        </div>
    </div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>