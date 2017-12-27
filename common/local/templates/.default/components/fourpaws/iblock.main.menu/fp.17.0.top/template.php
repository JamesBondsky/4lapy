<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Главное меню сайта
 *
 * @updated: 26.12.2017
 */

if (!$arResult['MENU_TREE']) {
    return;
}

$sArrowDownSwg_10_10 = (new \FourPaws\Decorators\SvgDecorator('icon-arrow-down', 10, 10))->__toString();
$sArrowDownSwg_6_10 = (new \FourPaws\Decorators\SvgDecorator('icon-arrow-down', 6, 10))->__toString();

$sArrowDownIco = '<span class="b-icon b-icon--more b-icon--orange b-icon--left-5">'.$sArrowDownSwg_10_10.'</span>';
$sArrowDownOrangeIco = '<span class="b-icon b-icon--back-mobile b-icon--orange">'.$sArrowDownSwg_10_10.'</span>';
$sArrowDownIcoSecond = '<span class="b-icon b-icon--menu-main">'.$sArrowDownSwg_6_10.'</span>';
$sArrowDownIcoThird = '<span class="b-icon">'.$sArrowDownSwg_6_10.'</span>';
$sArrowDownIcoFourth = '<span class="b-icon b-icon--menu-main b-icon--none-desktop">'.$sArrowDownSwg_6_10.'</span>';

?><nav class="b-menu js-nav-first-mobile">
    <ul class="b-menu__list"><?
        foreach ($arResult['MENU_TREE'] as $arItem) {
            if ($arItem['NESTED']) {
                $sAddClass1 = $arItem['IS_BRAND_MENU'] ? ' js-menu-brand-mobile' : ' js-menu-pet-mobile';
                $sAddClass2 = $arItem['IS_BRAND_MENU'] ? ' js-open-brand-mobile' : ' js-open-step-mobile';
                ?><li class="b-menu__item b-menu__item--more<?=$sAddClass1?>">
                    <a class="b-menu__link b-menu__link--more js-open-main-menu<?=$sAddClass2?>"<?=$arItem['_LINK_ATTR1_']?> href="<?=$arItem['_URL_']?>"><?
                        echo $arItem['_TEXT_'];
                        echo $sArrowDownIco;
                    ?></a>
                </li><?
            } else {
                ?><li class="b-menu__item">
                    <a class="b-menu__link"<?=$arItem['_LINK_ATTR1_']?> href="<?=$arItem['_URL_']?>"><?=$arItem['_TEXT_']?></a>
                </li><?
            }
        }
    ?></ul>
</nav><?

/**
 * Dropdown-меню
 */
ob_start();

foreach ($arResult['MENU_TREE'] as $arFirstLevelItem) {
    if (!$arFirstLevelItem['NESTED']) {
        continue;
    }

    if (!$arFirstLevelItem['IS_BRAND_MENU']) {
        ?><div class="b-menu-dropdown js-menu-dropdown js-menu-pet-desktop">
            <div class="b-container">
                <ul class="b-menu-main js-dropdown-menu js-permutation-second-menu js-step-mobile">
                    <li class="b-back-link">
                        <a class="b-back-link__link js-back-submenu"<?=$arFirstLevelItem['_LINK_ATTR2_']?> href="<?=$arFirstLevelItem['_URL_']?>"><?
                            echo $sArrowDownOrangeIco;
                            echo $arFirstLevelItem['_TEXT_'];
                        ?></a>
                    </li><?
                    foreach($arFirstLevelItem['NESTED'] as $arSecondLevelItem) {
                        ?><li class="b-menu-main__item">
                            <a class="b-menu-main__link js-active-submenu js-open-step-mobile"<?=$arSecondLevelItem['_LINK_ATTR2_']?> href="<?=$arSecondLevelItem['_URL_']?>"><?
                                echo $arSecondLevelItem['_TEXT_'];
                                echo $sArrowDownIcoSecond;
                            ?></a><?

                            ?><div class="b-menu-main__submenu js-submenu js-step-mobile">
                                <div class="b-back-link">
                                    <a class="b-back-link__link js-back-submenu"<?=$arSecondLevelItem['_LINK_ATTR2_']?> href="<?=$arSecondLevelItem['_URL_']?>"><?
                                        echo $sArrowDownOrangeIco;
                                        echo $arSecondLevelItem['_TEXT_'];
                                    ?></a>
                                </div><?
                                if ($arSecondLevelItem['NESTED']) {
                                    ?><div class="b-submenu-column"><?
                                        foreach($arSecondLevelItem['NESTED'] as $arThirdLevelItem) {
                                            ?><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu"<?=$arThirdLevelItem['_LINK_ATTR2_']?> href="<?=$arThirdLevelItem['_URL_']?>">
                                                <span class="b-link__text b-link__text--submenu"><?=$arThirdLevelItem['_TEXT_']?></span><?
                                                echo $sArrowDownIcoThird;
                                            ?></a>
                                            <ul class="b-submenu-column__list js-step-mobile">
                                                <li class="b-back-link">
                                                    <a class="b-back-link__link js-back-submenu"<?=$arThirdLevelItem['_LINK_ATTR2_']?> href="<?=$arThirdLevelItem['_URL_']?>"><?
                                                        echo $sArrowDownOrangeIco;
                                                        echo $arThirdLevelItem['_TEXT_'];
                                                    ?></a>
                                                </li><?
                                                if ($arThirdLevelItem['NESTED']) {
                                                    foreach($arThirdLevelItem['NESTED'] as $arFourthLevelItem) {
                                                        ?><li class="b-submenu-column__item">
                                                            <a class="b-submenu-column__link"<?=$arFourthLevelItem['_LINK_ATTR1_']?> href="<?=$arFourthLevelItem['_URL_']?>"><?
                                                                echo $arFourthLevelItem['_TEXT_'];
                                                                echo $sArrowDownIcoFourth;
                                                            ?></a>
                                                        </li><?
                                                    }
                                                }
                                            ?></ul><?
                                        }
                                    ?></div><?
                                }

                ?><div class="b-menu-main__popular-brand">
                  <div class="b-menu-main__title js-open-step-mobile">
                      <a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu" href="javascript:void(0)" title="Популярные бренды">
                          <span class="b-link__text b-link__text--brand-menu">Популярные бренды</span>
                          <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                              </use>
                            </svg>
                          </span>
                      </a>
                  </div>
                  <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile">
                    <div class="b-back-link">
                        <a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды">
                            <span class="b-icon b-icon--back-mobile b-icon--orange">
                              <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                                </use>
                              </svg>
                            </span>
                            Популярные бренды
                        </a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown">
                        <a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Royal Canin" href="javascript:void(0);">
                            <span class="b-popular-brand-item__text">Royal Canin</span>
                            <span class="b-icon b-icon--brand-menu">
                              <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                                </use>
                              </svg>
                            </span>
                            <img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/royal-canin.jpg" alt="Royal Canin" title="Royal Canin"/>
                        </a>
                    </div>
                  </div><?


                            ?></div>
                        </li><?
                    }
                ?></ul>
            </div>
        </div><?
        ?><div class="b-menu-mobile js-menu-mobile js-step-mobile"></div><?
    } else {
        ?><div class="b-menu-dropdown b-menu-dropdown--brands js-menu-dropdown js-menu-brands-desktop">
            <div class="b-menu-brands js-menu-brand-content">
            <div class="b-back-link b-back-link--brands">
                <a class="b-back-link__link js-close-popup js-close-brand-mobile"<?=$arFirstLevelItem['_LINK_ATTR2_']?> href="<?=$arFirstLevelItem['_URL_']?>"><?
                    echo $sArrowDownOrangeIco;
                    echo $arFirstLevelItem['_TEXT_'];
                ?></a>
            </div>
            <div class="b-container">
                <div class="b-menu-brands__nav">
                    <?
                    // алфавитный указатель
                    ?>
                      <div class="b-link-list b-link-list--menu js-scroll-x-menu">
                        <div class="b-link-list__wrapper"><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">#</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">A</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">B</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">C</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">D</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">E</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">F</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">G</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">H</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">I</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">J</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">K</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">L</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">M</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">N</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">O</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu active" href="javascript:void(0)" title="">P</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Q</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">R</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">S</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">T</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">U</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">V</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">W</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">X</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Y</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Z</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">А</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Б</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">В</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Г</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Д</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Е</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ё</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ж</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">З</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">И</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Й</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">К</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Л</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">М</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Н</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">О</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">П</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Р</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">С</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Т</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">У</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ф</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Х</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ц</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ч</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ш</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Щ</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ъ</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ы</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ь</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Э</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ю</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Я</a></div>
                      </div>
                </div>






            <div class="b-menu-brands__brand-block js-scroll-x-brands">
              <ul class="b-menu-brands__group-list">
                <li class="b-menu-brands__group b-menu-brands__group--mobile-show">
                  <ul class="b-menu-brands__name-list b-menu-brands__name-list--no-top">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link js-active-submenu js-open-step-mobile" href="javascript:void(0)" title="Популярные бренды">Популярные бренды</a>
                      <div class="b-menu-main__submenu js-submenu js-step-mobile">
                        <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды"><span class="b-icon b-icon--back-mobile b-icon--orange">
                          <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                            <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                            </use>
                          </svg></span>Популярные бренды</a>
                        </div>
                        <ul class="b-menu-brands__name-list b-menu-brands__name-list--no-top">
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Hill's">Hill's</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect fit">Perfect fit</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Purina">Purina</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Whiskas">Whiskas</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Felix">Felix</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Bozita">Bozita</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Acana">Acana</a>
                          </li>
                        </ul>
                      </div>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">#</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Adquuin">Adquuin</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="A-name">A-name</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="A-Pussy-cat">A-Pussy-cat</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">A</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pro Den">Pro Den</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Purina">Purina</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pussy-cat">Pussy-cat</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">P</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect Fit">Perfect Fit</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Probiotics International Ltd.">Probiotics International Ltd.</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect Fit">Perfect Fit</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect Fit">Perfect Fit</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect Fit">Perfect Fit</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect Fit">Perfect Fit</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">R</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="ReptiZoo">ReptiZoo</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Rinti">Rinti</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Rio">Rio</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="ReptiZoo">ReptiZoo</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Rinti">Rinti</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Rio">Rio</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">S</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="San Pet">San Pet</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Sanal">Sanal</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="San Pet">San Pet</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Sanal">Sanal</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="San Pet">San Pet</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Sanal">Sanal</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="San Pet">San Pet</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Sanal">Sanal</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">T</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Teddy Pets">Teddy Pets</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Trainer">Trainer</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Teddy Pets">Teddy Pets</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Trainer">Trainer</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">U</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="U-name">U-name</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">V</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Vand name">Vand name</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Vand name">Vand name</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Vand name">Vand name</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Vand name">Vand name</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">А</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Аалашиха">Аалашиха</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="р.п. Аыково">р.п. Аыково</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Аалашиха">Аалашиха</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="р.п. Аыково">р.п. Аыково</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Аалашиха">Аалашиха</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="р.п. Аыково">р.п. Аыково</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">Б</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Бидное">Бидное</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Боскресенск">Боскресенск</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Бидное">Бидное</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Боскресенск">Боскресенск</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">В</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Видное">Видное</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Воскресенск">Воскресенск</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Видное">Видное</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Воскресенск">Воскресенск</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">Г</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Гелково">Гелково</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Гербинка">Гербинка</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">Д</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дзержинский">Дзержинский</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дмитров">Дмитров</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Долгопрудный">Долгопрудный</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дубна">Дубна</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дзержинский">Дзержинский</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дмитров">Дмитров</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Долгопрудный">Долгопрудный</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дубна">Дубна</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дзержинский">Дзержинский</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дмитров">Дмитров</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Долгопрудный">Долгопрудный</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дубна">Дубна</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">Ю</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Юбилейный">Юбилейный</a>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
            <div class="b-menu-brands__popular-brand">
              <div class="b-menu-brands__title">Популярные бренды
              </div>
              <div class="b-popular-brand b-popular-brand--brands">
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Hill's" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/hills.jpg" alt="Hill's" title="Hill's"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Perfect fit" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/perfect-fit.jpg" alt="Perfect fit" title="Perfect fit"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Purina" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/purina.jpg" alt="Purina" title="Purina"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Whiskas" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Felix" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/felix.jpg" alt="Felix" title="Felix"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Felix" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/felix.jpg" alt="Felix" title="Felix"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Bozita" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/bozita.jpg" alt="Bozita" title="Bozita"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Acana" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/acana.jpg" alt="Acana" title="Acana"/></a>
                </div>
              </div>
            </div>



            </div>
        </div><?
    }
}

/*
?>
      <div class="b-menu-dropdown js-menu-dropdown js-menu-pet-desktop">
        <div class="b-container">
          <ul class="b-menu-main js-dropdown-menu js-permutation-second-menu js-step-mobile">
            <li class="b-back-link">
                <a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Товары по питомцу">
                    <span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg>
                    </span>
                    Товары по питомцу
                </a>
            </li>
            <li class="b-menu-main__item">
                <a class="b-menu-main__link js-active-submenu js-open-step-mobile" href="javascript:void(0);" title="Кошки">
                    Кошки
                    <span class="b-icon b-icon--menu-main">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg>
                    </span>
                </a>
                <div class="b-menu-main__submenu js-submenu js-step-mobile">
                    <div class="b-back-link">
                        <a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Кошки">
                            <span class="b-icon b-icon--back-mobile b-icon--orange">
                              <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                                </use>
                              </svg>
                            </span>
                            Кошки
                        </a>
                    </div>
                    <div class="b-submenu-column">
                        <a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Корм">
                            <span class="b-link__text b-link__text--submenu">Корм</span>
                            <span class="b-icon">
                              <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                                </use>
                              </svg>
                            </span>
                        </a>
                        <ul class="b-submenu-column__list js-step-mobile">
                            <li class="b-back-link">
                                <a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Корм">
                                    <span class="b-icon b-icon--back-mobile b-icon--orange">
                                        <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                                            <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                                            </use>
                                          </svg>
                                    </span>
                                    Корм
                                </a>
                            </li>


                            <li class="b-submenu-column__item">
                                <a class="b-submenu-column__link" href="javascript:void(0);">
                                    Сухой
                                    <span class="b-icon b-icon--menu-main b-icon--none-desktop">
                                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                                        </use>
                                      </svg>
                                    </span>
                                </a>
                            </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Консервы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Диетический<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кормовая добавка и молоко<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Лакомства"><span class="b-link__text b-link__text--submenu">Лакомства</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Мясные, вяленные, печеные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для выведения шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для чистки зубов<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сушеные натуральные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Колбаски<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кошачья мята<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Наполнители"><span class="b-link__text b-link__text--submenu">Наполнители</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Древесный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Комкующийся<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Силикагель<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Впитывающий<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Ароматизированный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Минеральный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Бетонит<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Содержание и уход"><span class="b-link__text b-link__text--submenu">Содержание и уход</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Когтеточки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Туалеты, лотки, совочки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Лежаки и домики<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Игрушки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Товары для груминга<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Миски, кормушки, поилки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Ветаптека"><span class="b-link__text b-link__text--submenu">Ветаптека</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Защита от блох и клещей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Противопаразитные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Вывод шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Капли и лосьоны для ушей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Гомеопатия и фитопрепараты<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-menu-main__popular-brand">
                  <div class="b-menu-main__title js-open-step-mobile"><a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu" href="javascript:void(0)" title="Популярные бренды"><span class="b-link__text b-link__text--brand-menu">Популярные бренды</span><span class="b-icon">
                    <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                      <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                      </use>
                    </svg></span></a>
                  </div>
                  <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile">
                    <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Популярные бренды</a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Royal Canin" href="javascript:void(0);"><span class="b-popular-brand-item__text">Royal Canin</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/royal-canin.jpg" alt="Royal Canin" title="Royal Canin"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Hill's" href="javascript:void(0);"><span class="b-popular-brand-item__text">Hill's</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/hills.jpg" alt="Hill's" title="Hill's"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Purina" href="javascript:void(0);"><span class="b-popular-brand-item__text">Purina</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/purina.jpg" alt="Purina" title="Purina"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Whiskas" href="javascript:void(0);"><span class="b-popular-brand-item__text">Whiskas</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Eukanuba" href="javascript:void(0);"><span class="b-popular-brand-item__text">Eukanuba</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/eukanuba.jpg" alt="Eukanuba" title="Eukanuba"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Bozita" href="javascript:void(0);"><span class="b-popular-brand-item__text">Bozita</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/bozita.jpg" alt="Bozita" title="Bozita"/></a>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="b-menu-main__item"><a class="b-menu-main__link js-active-submenu js-open-step-mobile" href="javascript:void(0);" title="Собаки">Собаки<span class="b-icon b-icon--menu-main">
              <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                </use>
              </svg></span></a>
              <div class="b-menu-main__submenu js-submenu js-step-mobile">
                <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Кошки"><span class="b-icon b-icon--back-mobile b-icon--orange">
                  <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span>Кошки</a>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Корм1"><span class="b-link__text b-link__text--submenu">Корм1</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сухой<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Консервы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Диетический<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кормовая добавка и молоко<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Лакомства"><span class="b-link__text b-link__text--submenu">Лакомства</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Мясные, вяленные, печеные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для выведения шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для чистки зубов<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сушеные натуральные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Колбаски<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кошачья мята<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Наполнители"><span class="b-link__text b-link__text--submenu">Наполнители</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Древесный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Комкующийся<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Силикагель<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Впитывающий<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Ароматизированный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Минеральный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Бетонит<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Содержание и уход"><span class="b-link__text b-link__text--submenu">Содержание и уход</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Когтеточки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Туалеты, лотки, совочки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Лежаки и домики<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Игрушки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Товары для груминга<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Миски, кормушки, поилки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Ветаптека"><span class="b-link__text b-link__text--submenu">Ветаптека</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Защита от блох и клещей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Противопаразитные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Вывод шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Капли и лосьоны для ушей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Гомеопатия и фитопрепараты<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-menu-main__popular-brand">
                  <div class="b-menu-main__title js-open-step-mobile"><a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu" href="javascript:void(0)" title="Популярные бренды"><span class="b-link__text b-link__text--brand-menu">Популярные бренды</span><span class="b-icon">
                    <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                      <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                      </use>
                    </svg></span></a>
                  </div>
                  <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile">
                    <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Популярные бренды</a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Royal Canin" href="javascript:void(0);"><span class="b-popular-brand-item__text">Royal Canin</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/royal-canin.jpg" alt="Royal Canin" title="Royal Canin"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Hill's" href="javascript:void(0);"><span class="b-popular-brand-item__text">Hill's</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/hills.jpg" alt="Hill's" title="Hill's"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Purina" href="javascript:void(0);"><span class="b-popular-brand-item__text">Purina</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/purina.jpg" alt="Purina" title="Purina"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Whiskas" href="javascript:void(0);"><span class="b-popular-brand-item__text">Whiskas</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Eukanuba" href="javascript:void(0);"><span class="b-popular-brand-item__text">Eukanuba</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/eukanuba.jpg" alt="Eukanuba" title="Eukanuba"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Bozita" href="javascript:void(0);"><span class="b-popular-brand-item__text">Bozita</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/bozita.jpg" alt="Bozita" title="Bozita"/></a>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="b-menu-main__item"><a class="b-menu-main__link js-active-submenu js-open-step-mobile" href="javascript:void(0);" title="Грызуны и хорьки">Грызуны и хорьки<span class="b-icon b-icon--menu-main">
              <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                </use>
              </svg></span></a>
              <div class="b-menu-main__submenu js-submenu js-step-mobile">
                <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Кошки"><span class="b-icon b-icon--back-mobile b-icon--orange">
                  <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span>Кошки</a>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Корм2"><span class="b-link__text b-link__text--submenu">Корм2</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сухой<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Консервы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Диетический<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кормовая добавка и молоко<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Лакомства"><span class="b-link__text b-link__text--submenu">Лакомства</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Мясные, вяленные, печеные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для выведения шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для чистки зубов<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сушеные натуральные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Колбаски<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кошачья мята<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Наполнители"><span class="b-link__text b-link__text--submenu">Наполнители</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Древесный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Комкующийся<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Силикагель<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Впитывающий<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Ароматизированный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Минеральный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Бетонит<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Содержание и уход"><span class="b-link__text b-link__text--submenu">Содержание и уход</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Когтеточки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Туалеты, лотки, совочки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Лежаки и домики<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Игрушки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Товары для груминга<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Миски, кормушки, поилки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Ветаптека"><span class="b-link__text b-link__text--submenu">Ветаптека</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Защита от блох и клещей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Противопаразитные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Вывод шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Капли и лосьоны для ушей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Гомеопатия и фитопрепараты<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-menu-main__popular-brand">
                  <div class="b-menu-main__title js-open-step-mobile"><a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu" href="javascript:void(0)" title="Популярные бренды"><span class="b-link__text b-link__text--brand-menu">Популярные бренды</span><span class="b-icon">
                    <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                      <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                      </use>
                    </svg></span></a>
                  </div>
                  <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile">
                    <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Популярные бренды</a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Royal Canin" href="javascript:void(0);"><span class="b-popular-brand-item__text">Royal Canin</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/royal-canin.jpg" alt="Royal Canin" title="Royal Canin"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Hill's" href="javascript:void(0);"><span class="b-popular-brand-item__text">Hill's</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/hills.jpg" alt="Hill's" title="Hill's"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Purina" href="javascript:void(0);"><span class="b-popular-brand-item__text">Purina</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/purina.jpg" alt="Purina" title="Purina"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Whiskas" href="javascript:void(0);"><span class="b-popular-brand-item__text">Whiskas</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Eukanuba" href="javascript:void(0);"><span class="b-popular-brand-item__text">Eukanuba</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/eukanuba.jpg" alt="Eukanuba" title="Eukanuba"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Bozita" href="javascript:void(0);"><span class="b-popular-brand-item__text">Bozita</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/bozita.jpg" alt="Bozita" title="Bozita"/></a>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="b-menu-main__item"><a class="b-menu-main__link js-active-submenu js-open-step-mobile" href="javascript:void(0);" title="Птицы">Птицы<span class="b-icon b-icon--menu-main">
              <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                </use>
              </svg></span></a>
              <div class="b-menu-main__submenu js-submenu js-step-mobile">
                <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Кошки"><span class="b-icon b-icon--back-mobile b-icon--orange">
                  <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span>Кошки</a>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Корм3"><span class="b-link__text b-link__text--submenu">Корм3</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сухой<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Консервы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Диетический<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кормовая добавка и молоко<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Лакомства"><span class="b-link__text b-link__text--submenu">Лакомства</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Мясные, вяленные, печеные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для выведения шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для чистки зубов<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сушеные натуральные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Колбаски<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кошачья мята<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Наполнители"><span class="b-link__text b-link__text--submenu">Наполнители</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Древесный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Комкующийся<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Силикагель<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Впитывающий<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Ароматизированный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Минеральный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Бетонит<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Содержание и уход"><span class="b-link__text b-link__text--submenu">Содержание и уход</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Когтеточки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Туалеты, лотки, совочки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Лежаки и домики<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Игрушки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Товары для груминга<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Миски, кормушки, поилки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Ветаптека"><span class="b-link__text b-link__text--submenu">Ветаптека</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Защита от блох и клещей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Противопаразитные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Вывод шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Капли и лосьоны для ушей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Гомеопатия и фитопрепараты<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-menu-main__popular-brand">
                  <div class="b-menu-main__title js-open-step-mobile"><a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu" href="javascript:void(0)" title="Популярные бренды"><span class="b-link__text b-link__text--brand-menu">Популярные бренды</span><span class="b-icon">
                    <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                      <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                      </use>
                    </svg></span></a>
                  </div>
                  <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile">
                    <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Популярные бренды</a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Royal Canin" href="javascript:void(0);"><span class="b-popular-brand-item__text">Royal Canin</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/royal-canin.jpg" alt="Royal Canin" title="Royal Canin"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Hill's" href="javascript:void(0);"><span class="b-popular-brand-item__text">Hill's</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/hills.jpg" alt="Hill's" title="Hill's"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Purina" href="javascript:void(0);"><span class="b-popular-brand-item__text">Purina</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/purina.jpg" alt="Purina" title="Purina"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Whiskas" href="javascript:void(0);"><span class="b-popular-brand-item__text">Whiskas</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Eukanuba" href="javascript:void(0);"><span class="b-popular-brand-item__text">Eukanuba</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/eukanuba.jpg" alt="Eukanuba" title="Eukanuba"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Bozita" href="javascript:void(0);"><span class="b-popular-brand-item__text">Bozita</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/bozita.jpg" alt="Bozita" title="Bozita"/></a>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="b-menu-main__item"><a class="b-menu-main__link js-active-submenu js-open-step-mobile" href="javascript:void(0);" title="Рыбы">Рыбы<span class="b-icon b-icon--menu-main">
              <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                </use>
              </svg></span></a>
              <div class="b-menu-main__submenu js-submenu js-step-mobile">
                <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Кошки"><span class="b-icon b-icon--back-mobile b-icon--orange">
                  <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span>Кошки</a>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Корм4"><span class="b-link__text b-link__text--submenu">Корм4</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сухой<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Консервы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Диетический<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кормовая добавка и молоко<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Лакомства"><span class="b-link__text b-link__text--submenu">Лакомства</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Мясные, вяленные, печеные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для выведения шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для чистки зубов<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сушеные натуральные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Колбаски<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кошачья мята<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Наполнители"><span class="b-link__text b-link__text--submenu">Наполнители</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Древесный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Комкующийся<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Силикагель<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Впитывающий<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Ароматизированный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Минеральный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Бетонит<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Содержание и уход"><span class="b-link__text b-link__text--submenu">Содержание и уход</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Когтеточки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Туалеты, лотки, совочки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Лежаки и домики<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Игрушки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Товары для груминга<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Миски, кормушки, поилки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Ветаптека"><span class="b-link__text b-link__text--submenu">Ветаптека</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Защита от блох и клещей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Противопаразитные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Вывод шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Капли и лосьоны для ушей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Гомеопатия и фитопрепараты<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-menu-main__popular-brand">
                  <div class="b-menu-main__title js-open-step-mobile"><a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu" href="javascript:void(0)" title="Популярные бренды"><span class="b-link__text b-link__text--brand-menu">Популярные бренды</span><span class="b-icon">
                    <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                      <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                      </use>
                    </svg></span></a>
                  </div>
                  <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile">
                    <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Популярные бренды</a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Royal Canin" href="javascript:void(0);"><span class="b-popular-brand-item__text">Royal Canin</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/royal-canin.jpg" alt="Royal Canin" title="Royal Canin"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Hill's" href="javascript:void(0);"><span class="b-popular-brand-item__text">Hill's</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/hills.jpg" alt="Hill's" title="Hill's"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Purina" href="javascript:void(0);"><span class="b-popular-brand-item__text">Purina</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/purina.jpg" alt="Purina" title="Purina"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Whiskas" href="javascript:void(0);"><span class="b-popular-brand-item__text">Whiskas</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Eukanuba" href="javascript:void(0);"><span class="b-popular-brand-item__text">Eukanuba</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/eukanuba.jpg" alt="Eukanuba" title="Eukanuba"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Bozita" href="javascript:void(0);"><span class="b-popular-brand-item__text">Bozita</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/bozita.jpg" alt="Bozita" title="Bozita"/></a>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="b-menu-main__item"><a class="b-menu-main__link js-active-submenu js-open-step-mobile" href="javascript:void(0);" title="Рептилии">Рептилии<span class="b-icon b-icon--menu-main">
              <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                </use>
              </svg></span></a>
              <div class="b-menu-main__submenu js-submenu js-step-mobile">
                <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Кошки"><span class="b-icon b-icon--back-mobile b-icon--orange">
                  <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span>Кошки</a>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Корм5"><span class="b-link__text b-link__text--submenu">Корм5</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сухой<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Консервы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Диетический<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кормовая добавка и молоко<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Лакомства"><span class="b-link__text b-link__text--submenu">Лакомства</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Мясные, вяленные, печеные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для выведения шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для чистки зубов<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сушеные натуральные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Колбаски<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кошачья мята<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Наполнители"><span class="b-link__text b-link__text--submenu">Наполнители</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Древесный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Комкующийся<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Силикагель<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Впитывающий<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Ароматизированный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Минеральный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Бетонит<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Содержание и уход"><span class="b-link__text b-link__text--submenu">Содержание и уход</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Когтеточки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Туалеты, лотки, совочки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Лежаки и домики<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Игрушки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Товары для груминга<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Миски, кормушки, поилки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Ветаптека"><span class="b-link__text b-link__text--submenu">Ветаптека</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Защита от блох и клещей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Противопаразитные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Вывод шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Капли и лосьоны для ушей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Гомеопатия и фитопрепараты<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-menu-main__popular-brand">
                  <div class="b-menu-main__title js-open-step-mobile"><a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu" href="javascript:void(0)" title="Популярные бренды"><span class="b-link__text b-link__text--brand-menu">Популярные бренды</span><span class="b-icon">
                    <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                      <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                      </use>
                    </svg></span></a>
                  </div>
                  <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile">
                    <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Популярные бренды</a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Royal Canin" href="javascript:void(0);"><span class="b-popular-brand-item__text">Royal Canin</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/royal-canin.jpg" alt="Royal Canin" title="Royal Canin"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Hill's" href="javascript:void(0);"><span class="b-popular-brand-item__text">Hill's</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/hills.jpg" alt="Hill's" title="Hill's"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Purina" href="javascript:void(0);"><span class="b-popular-brand-item__text">Purina</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/purina.jpg" alt="Purina" title="Purina"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Whiskas" href="javascript:void(0);"><span class="b-popular-brand-item__text">Whiskas</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Eukanuba" href="javascript:void(0);"><span class="b-popular-brand-item__text">Eukanuba</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/eukanuba.jpg" alt="Eukanuba" title="Eukanuba"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Bozita" href="javascript:void(0);"><span class="b-popular-brand-item__text">Bozita</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/bozita.jpg" alt="Bozita" title="Bozita"/></a>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="b-menu-main__item"><a class="b-menu-main__link js-active-submenu js-open-step-mobile" href="javascript:void(0);" title="Котята">Котята<span class="b-icon b-icon--menu-main">
              <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                </use>
              </svg></span></a>
              <div class="b-menu-main__submenu js-submenu js-step-mobile">
                <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Кошки"><span class="b-icon b-icon--back-mobile b-icon--orange">
                  <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span>Кошки</a>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Корм6"><span class="b-link__text b-link__text--submenu">Корм6</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сухой<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Консервы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Диетический<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кормовая добавка и молоко<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Лакомства"><span class="b-link__text b-link__text--submenu">Лакомства</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Мясные, вяленные, печеные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для выведения шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для чистки зубов<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сушеные натуральные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Колбаски<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кошачья мята<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Наполнители"><span class="b-link__text b-link__text--submenu">Наполнители</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Древесный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Комкующийся<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Силикагель<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Впитывающий<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Ароматизированный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Минеральный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Бетонит<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Содержание и уход"><span class="b-link__text b-link__text--submenu">Содержание и уход</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Когтеточки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Туалеты, лотки, совочки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Лежаки и домики<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Игрушки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Товары для груминга<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Миски, кормушки, поилки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Ветаптека"><span class="b-link__text b-link__text--submenu">Ветаптека</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Защита от блох и клещей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Противопаразитные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Вывод шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Капли и лосьоны для ушей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Гомеопатия и фитопрепараты<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-menu-main__popular-brand">
                  <div class="b-menu-main__title js-open-step-mobile"><a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu" href="javascript:void(0)" title="Популярные бренды"><span class="b-link__text b-link__text--brand-menu">Популярные бренды</span><span class="b-icon">
                    <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                      <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                      </use>
                    </svg></span></a>
                  </div>
                  <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile">
                    <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Популярные бренды</a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Royal Canin" href="javascript:void(0);"><span class="b-popular-brand-item__text">Royal Canin</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/royal-canin.jpg" alt="Royal Canin" title="Royal Canin"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Hill's" href="javascript:void(0);"><span class="b-popular-brand-item__text">Hill's</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/hills.jpg" alt="Hill's" title="Hill's"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Purina" href="javascript:void(0);"><span class="b-popular-brand-item__text">Purina</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/purina.jpg" alt="Purina" title="Purina"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Whiskas" href="javascript:void(0);"><span class="b-popular-brand-item__text">Whiskas</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Eukanuba" href="javascript:void(0);"><span class="b-popular-brand-item__text">Eukanuba</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/eukanuba.jpg" alt="Eukanuba" title="Eukanuba"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Bozita" href="javascript:void(0);"><span class="b-popular-brand-item__text">Bozita</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/bozita.jpg" alt="Bozita" title="Bozita"/></a>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="b-menu-main__item"><a class="b-menu-main__link js-active-submenu js-open-step-mobile" href="javascript:void(0);" title="Щенки">Щенки<span class="b-icon b-icon--menu-main">
              <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                </use>
              </svg></span></a>
              <div class="b-menu-main__submenu js-submenu js-step-mobile">
                <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Кошки"><span class="b-icon b-icon--back-mobile b-icon--orange">
                  <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span>Кошки</a>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Корм7"><span class="b-link__text b-link__text--submenu">Корм7</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сухой<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Консервы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Диетический<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кормовая добавка и молоко<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Лакомства"><span class="b-link__text b-link__text--submenu">Лакомства</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Мясные, вяленные, печеные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для выведения шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Для чистки зубов<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Сушеные натуральные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Колбаски<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Кошачья мята<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Наполнители"><span class="b-link__text b-link__text--submenu">Наполнители</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Древесный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Комкующийся<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Силикагель<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Впитывающий<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Ароматизированный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Минеральный<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Бетонит<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Содержание и уход"><span class="b-link__text b-link__text--submenu">Содержание и уход</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Когтеточки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Туалеты, лотки, совочки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Лежаки и домики<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Игрушки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Товары для груминга<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Миски, кормушки, поилки<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-submenu-column"><a class="b-link b-link--submenu js-open-step-mobile js-open-step-mobile--submenu" href="javascript:void(0)" title="Ветаптека"><span class="b-link__text b-link__text--submenu">Ветаптека</span><span class="b-icon">
                  <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                    </use>
                  </svg></span></a>
                  <ul class="b-submenu-column__list js-step-mobile">
                    <li class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Лакомства"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Лакомства</a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Защита от блох и клещей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Противопаразитные<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Витамины и минералы<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Вывод шерсти<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Капли и лосьоны для ушей<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                    <li class="b-submenu-column__item"><a class="b-submenu-column__link" href="javascript:void(0);">Гомеопатия и фитопрепараты<span class="b-icon b-icon--menu-main b-icon--none-desktop">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </li>
                  </ul>
                </div>
                <div class="b-menu-main__popular-brand">
                  <div class="b-menu-main__title js-open-step-mobile"><a class="b-link b-link--brand-menu js-not-href js-not-href--brand-menu" href="javascript:void(0)" title="Популярные бренды"><span class="b-link__text b-link__text--brand-menu">Популярные бренды</span><span class="b-icon">
                    <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                      <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                      </use>
                    </svg></span></a>
                  </div>
                  <div class="b-popular-brand b-popular-brand--flex b-popular-brand--menu-dropdown js-step-mobile">
                    <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды"><span class="b-icon b-icon--back-mobile b-icon--orange">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span>Популярные бренды</a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Royal Canin" href="javascript:void(0);"><span class="b-popular-brand-item__text">Royal Canin</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/royal-canin.jpg" alt="Royal Canin" title="Royal Canin"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Hill's" href="javascript:void(0);"><span class="b-popular-brand-item__text">Hill's</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/hills.jpg" alt="Hill's" title="Hill's"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Purina" href="javascript:void(0);"><span class="b-popular-brand-item__text">Purina</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/purina.jpg" alt="Purina" title="Purina"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Whiskas" href="javascript:void(0);"><span class="b-popular-brand-item__text">Whiskas</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Eukanuba" href="javascript:void(0);"><span class="b-popular-brand-item__text">Eukanuba</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/eukanuba.jpg" alt="Eukanuba" title="Eukanuba"/></a>
                    </div>
                    <div class="b-popular-brand-item b-popular-brand-item--menu-dropdown"><a class="b-popular-brand-item__link b-popular-brand-item__link--menu-dropdown" title="Bozita" href="javascript:void(0);"><span class="b-popular-brand-item__text">Bozita</span><span class="b-icon b-icon--brand-menu">
                      <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span><img class="b-popular-brand-item__image js-image-wrapper" src="/images/content/bozita.jpg" alt="Bozita" title="Bozita"/></a>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div class="b-menu-mobile js-menu-mobile js-step-mobile">
      </div>
      <div class="b-menu-dropdown b-menu-dropdown--brands js-menu-dropdown js-menu-brands-desktop">
        <div class="b-menu-brands js-menu-brand-content">
          <div class="b-back-link b-back-link--brands"><a class="b-back-link__link js-close-popup js-close-brand-mobile" href="javascript:void(0);" title="Товары по бренду"><span class="b-icon b-icon--back-mobile b-icon--orange">
            <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
              <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
              </use>
            </svg></span>Товары по бренду</a>
          </div>
          <div class="b-container">
            <div class="b-menu-brands__nav">
              <div class="b-link-list b-link-list--menu js-scroll-x-menu">
                <div class="b-link-list__wrapper"><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">#</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">A</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">B</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">C</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">D</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">E</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">F</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">G</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">H</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">I</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">J</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">K</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">L</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">M</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">N</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">O</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu active" href="javascript:void(0)" title="">P</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Q</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">R</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">S</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">T</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">U</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">V</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">W</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">X</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Y</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Z</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">А</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Б</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">В</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Г</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Д</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Е</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ё</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ж</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">З</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">И</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Й</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">К</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Л</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">М</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Н</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">О</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">П</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Р</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">С</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Т</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">У</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ф</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Х</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ц</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ч</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ш</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Щ</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ъ</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ы</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ь</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Э</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Ю</a><a class="b-link-list__link b-link-list__link--menu js-scroll-x-menu" href="javascript:void(0)" title="">Я</a>
                </div>
              </div>
            </div>
            <div class="b-menu-brands__brand-block js-scroll-x-brands">
              <ul class="b-menu-brands__group-list">
                <li class="b-menu-brands__group b-menu-brands__group--mobile-show">
                  <ul class="b-menu-brands__name-list b-menu-brands__name-list--no-top">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link js-active-submenu js-open-step-mobile" href="javascript:void(0)" title="Популярные бренды">Популярные бренды</a>
                      <div class="b-menu-main__submenu js-submenu js-step-mobile">
                        <div class="b-back-link"><a class="b-back-link__link js-back-submenu" href="javascript:void(0);" title="Популярные бренды"><span class="b-icon b-icon--back-mobile b-icon--orange">
                          <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                            <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                            </use>
                          </svg></span>Популярные бренды</a>
                        </div>
                        <ul class="b-menu-brands__name-list b-menu-brands__name-list--no-top">
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Hill's">Hill's</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect fit">Perfect fit</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Purina">Purina</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Whiskas">Whiskas</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Felix">Felix</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Bozita">Bozita</a>
                          </li>
                          <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Acana">Acana</a>
                          </li>
                        </ul>
                      </div>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">#</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Adquuin">Adquuin</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="A-name">A-name</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="A-Pussy-cat">A-Pussy-cat</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">A</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pro Den">Pro Den</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Purina">Purina</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pussy-cat">Pussy-cat</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">P</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect Fit">Perfect Fit</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Probiotics International Ltd.">Probiotics International Ltd.</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect Fit">Perfect Fit</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect Fit">Perfect Fit</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect Fit">Perfect Fit</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Padovan">Padovan</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Pedigree">Pedigree</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Penn-Plax">Penn-Plax</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Perfect Fit">Perfect Fit</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">R</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="ReptiZoo">ReptiZoo</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Rinti">Rinti</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Rio">Rio</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="ReptiZoo">ReptiZoo</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Rinti">Rinti</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Rio">Rio</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">S</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="San Pet">San Pet</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Sanal">Sanal</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="San Pet">San Pet</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Sanal">Sanal</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="San Pet">San Pet</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Sanal">Sanal</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="San Pet">San Pet</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Sanal">Sanal</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">T</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Teddy Pets">Teddy Pets</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Trainer">Trainer</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Teddy Pets">Teddy Pets</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Trainer">Trainer</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">U</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="U-name">U-name</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">V</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Vand name">Vand name</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Vand name">Vand name</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Vand name">Vand name</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Vand name">Vand name</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">А</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Аалашиха">Аалашиха</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="р.п. Аыково">р.п. Аыково</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Аалашиха">Аалашиха</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="р.п. Аыково">р.п. Аыково</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Аалашиха">Аалашиха</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="р.п. Аыково">р.п. Аыково</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">Б</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Бидное">Бидное</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Боскресенск">Боскресенск</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Бидное">Бидное</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Боскресенск">Боскресенск</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">В</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Видное">Видное</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Воскресенск">Воскресенск</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Видное">Видное</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Воскресенск">Воскресенск</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">Г</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Гелково">Гелково</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Гербинка">Гербинка</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">Д</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дзержинский">Дзержинский</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дмитров">Дмитров</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Долгопрудный">Долгопрудный</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дубна">Дубна</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дзержинский">Дзержинский</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дмитров">Дмитров</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Долгопрудный">Долгопрудный</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дубна">Дубна</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дзержинский">Дзержинский</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дмитров">Дмитров</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Долгопрудный">Долгопрудный</a>
                    </li>
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Дубна">Дубна</a>
                    </li>
                  </ul>
                </li>
                <li class="b-menu-brands__group"><span class="b-menu-brands__litter js-brands-filter">Ю</span>
                  <ul class="b-menu-brands__name-list">
                    <li class="b-menu-brands__name"><a class="b-menu-brands__name-link" href="javascript:void(0)" title="Юбилейный">Юбилейный</a>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
            <div class="b-menu-brands__popular-brand">
              <div class="b-menu-brands__title">Популярные бренды
              </div>
              <div class="b-popular-brand b-popular-brand--brands">
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Hill's" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/hills.jpg" alt="Hill's" title="Hill's"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Perfect fit" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/perfect-fit.jpg" alt="Perfect fit" title="Perfect fit"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Purina" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/purina.jpg" alt="Purina" title="Purina"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Whiskas" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Felix" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/felix.jpg" alt="Felix" title="Felix"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Felix" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/felix.jpg" alt="Felix" title="Felix"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Bozita" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/bozita.jpg" alt="Bozita" title="Bozita"/></a>
                </div>
                <div class="b-popular-brand-item b-popular-brand-item--brands-menu"><a class="b-popular-brand-item__link b-popular-brand-item__link--brands-menu" title="Acana" href="javascript:void(0);"><img class="b-popular-brand-item__image js-image-wrapper" src="images/content/acana.jpg" alt="Acana" title="Acana"/></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
<?php
*/

$APPLICATION->AddViewContent('header_dropdown_menu', ob_get_clean());
