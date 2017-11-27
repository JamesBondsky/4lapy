<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetPageProperty('title', 'Зоомагазин Четыре лапы – сеть магазинов зоотоваров');
$APPLICATION->SetPageProperty('NOT_SHOW_NAV_CHAIN', 'Y');
$APPLICATION->SetTitle('Главная страница');

$APPLICATION->IncludeComponent('bitrix:news.list',
                               'index.slider',
                               [
                                   'COMPONENT_TEMPLATE'              => 'index.slider',
                                   'IBLOCK_TYPE'                     => 'publications',
                                   'IBLOCK_ID'                       => \Adv\Bitrixtools\Tools\Iblock\IblockUtils ::getIblockId('publications', 'banners'),//не проставлен символьный код
                                   'NEWS_COUNT'                      => '7',
                                   'SORT_BY1'                        => 'SORT',
                                   'SORT_ORDER1'                     => 'ASC',
                                   'SORT_BY2'                        => 'ACTIVE_FROM',
                                   'SORT_ORDER2'                     => 'DESC',
                                   'FILTER_NAME'                     => '',
                                   'FIELD_CODE'                      => [
                                       0 => 'NAME',
                                       1 => 'PREVIEW_PICTURE',
                                       2 => 'DETAIL_PICTURE',
                                       3 => '',
                                   ],
                                   'PROPERTY_CODE'                   => [
                                       0 => 'LINK',
                                       1 => '',
                                   ],
                                   'CHECK_DATES'                     => 'Y',
                                   'DETAIL_URL'                      => '',
                                   'AJAX_MODE'                       => 'N',
                                   'AJAX_OPTION_JUMP'                => 'N',
                                   'AJAX_OPTION_STYLE'               => 'N',
                                   'AJAX_OPTION_HISTORY'             => 'N',
                                   'AJAX_OPTION_ADDITIONAL'          => '',
                                   'CACHE_TYPE'                      => 'A',
                                   'CACHE_TIME'                      => '36000000',
                                   'CACHE_FILTER'                    => 'Y',
                                   'CACHE_GROUPS'                    => 'N',
                                   'PREVIEW_TRUNCATE_LEN'            => '',
                                   'ACTIVE_DATE_FORMAT'              => '',
                                   'SET_TITLE'                       => 'N',
                                   'SET_BROWSER_TITLE'               => 'N',
                                   'SET_META_KEYWORDS'               => 'N',
                                   'SET_META_DESCRIPTION'            => 'N',
                                   'SET_LAST_MODIFIED'               => 'N',
                                   'INCLUDE_IBLOCK_INTO_CHAIN'       => 'N',
                                   'ADD_SECTIONS_CHAIN'              => 'N',
                                   'HIDE_LINK_WHEN_NO_DETAIL'        => 'N',
                                   'PARENT_SECTION'                  => '',
                                   'PARENT_SECTION_CODE'             => '',
                                   'INCLUDE_SUBSECTIONS'             => 'N',
                                   'STRICT_SECTION_CHECK'            => 'N',
                                   'DISPLAY_DATE'                    => 'N',
                                   'DISPLAY_NAME'                    => 'N',
                                   'DISPLAY_PICTURE'                 => 'N',
                                   'DISPLAY_PREVIEW_TEXT'            => 'N',
                                   'PAGER_TEMPLATE'                  => '',
                                   'DISPLAY_TOP_PAGER'               => 'N',
                                   'DISPLAY_BOTTOM_PAGER'            => 'N',
                                   'PAGER_TITLE'                     => '',
                                   'PAGER_SHOW_ALWAYS'               => 'N',
                                   'PAGER_DESC_NUMBERING'            => 'N',
                                   'PAGER_DESC_NUMBERING_CACHE_TIME' => '',
                                   'PAGER_SHOW_ALL'                  => 'N',
                                   'PAGER_BASE_LINK_ENABLE'          => 'N',
                                   'SET_STATUS_404'                  => 'N',
                                   'SHOW_404'                        => 'N',
                                   'MESSAGE_404'                     => '',
                               ],
                               false,
                               ['HIDE_ICONS' => 'Y']);

/**
 * @todo Популярные товары. Заменить компонентом и удалить файл.
 */
require_once '_temp_popular.php';

/**
 * @todo Распродажа (товары со скидкой). Заменить компонентом и удалить файл.
 */
require_once '_temp_sale.php';

$APPLICATION->IncludeComponent('bitrix:main.include',
                               'index.advantages',
                               [
                                   'COMPONENT_TEMPLATE' => '.default',
                                   'AREA_FILE_SHOW'     => 'file',
                                   'PATH'               => '/local/include/blocks/index.advantages.php',
                                   'EDIT_TEMPLATE'      => '',
                               ],
                               false);

/**
 * Контейнер страницы. Не должен редактироваться в визуальном редакторе. Закрывается перед подключением подвала.
 */
echo '<div class="b-container">';

/**
 * @todo Популярные бренды. Заменить компонентом и удалить файл.
 */
require_once '_temp_sale.php';

$APPLICATION->IncludeComponent('bitrix:main.include',
                               'index.pet_block',
                               [
                                   'COMPONENT_TEMPLATE' => '.default',
                                   'AREA_FILE_SHOW'     => 'file',
                                   'PATH'               => '/local/include/blocks/index.pet_block.php',
                                   'EDIT_TEMPLATE'      => '',
                               ],
                               false);
/**
 * @todo Новости и события. Заменить компонентом и удалить файл.
 */
$APPLICATION->IncludeComponent('fourpaws:items.list',
                               '',
                               Array(
        'ACTIVE_DATE_FORMAT'     => 'j F Y',
        'AJAX_MODE'              => 'N',
        'AJAX_OPTION_ADDITIONAL' => '',
        'AJAX_OPTION_HISTORY'    => 'N',
        'AJAX_OPTION_JUMP'       => 'N',
        'AJAX_OPTION_STYLE'      => 'Y',
        'CACHE_FILTER'           => 'N',
        'CACHE_GROUPS'           => 'Y',
        'CACHE_TIME'             => '36000000',
        'CACHE_TYPE'             => 'A',
        'CHECK_DATES'            => 'Y',
        'FIELD_CODE'             => array(
            ''
        ),
        'FILTER_NAME'            => '',
        'IBLOCK_ID'                => array(
            \Adv\Bitrixtools\Tools\Iblock\IblockUtils::getIblockId('publications', 'news'),
            \Adv\Bitrixtools\Tools\Iblock\IblockUtils::getIblockId('publications', 'articles'),
            //\Adv\Bitrixtools\Tools\Iblock\IblockUtils::getIblockId('publications', 'cloubs_and_nurderis'),//Раскоментить когда добавится инфоблок
        ),
        'IBLOCK_TYPE'              => 'publications',
        'NEWS_COUNT'                => '7',
        'PREVIEW_TRUNCATE_LEN'            => '',
        'PROPERTY_CODE'                   => array('PUBLICATION_TYPE',
                                                   'VIDEO'
        ),
        'SET_LAST_MODIFIED'               => 'N',
        'SORT_BY1'                        => 'ACTIVE_FROM',
        'SORT_BY2'                        => 'SORT',
        'SORT_ORDER1'                     => 'DESC',
        'SORT_ORDER2'                     => 'ASC',
    )
);

/**
 * @todo Просмотренные товары. Заменить компонентом и удалить файл.
 */
require_once '_temp_viewed_products.php';

/**
 * Контейнер текста на странице.
 */
echo '<section class="b-adventure">'; ?><h2 class="b-title">Почему покупатели выбирают зоомагазин «Четыре лапы»</h2>
<dl class="b-adventure__item">
	<dt class="b-adventure__header-block"><span class="b-icon b-icon--adventure"> </span>
	<h4 class="b-adventure__header">Широкий ассортимент</h4>
 </dt>
	<dd class="b-adventure__description-block">
	<div class="b-adventure__text">
		 В интернет-магазине «Четыре Лапы» вы найдете все необходимое для организации питания и проживания своего питомца. Кошка не относится к числу прихотливых и слишком капризных домашних животных, но и для нее стоит покупать специальные средства.
	</div>
	<div class="b-accordion">
 <a class="b-accordion__header js-toggle-accordion" href="javascript:void(0);" title="Побробнее об ассортименте">Побробнее об ассортименте</a>
		<div class="b-accordion__block js-dropdown-block">
			 В интернет-магазине «Четыре Лапы» вы найдете все необходимое для организации питания и проживания своего питомца. Кошка не относится к числу прихотливых и слишком капризных домашних животных, но и для нее стоит покупать специальные средства. В интернет-магазине «Четыре Лапы» вы найдете все необходимое для организации питания и проживания своего питомца. Кошка не относится к числу прихотливых и слишком капризных домашних животных, но и для нее стоит покупать специальные средства. В интернет-магазине «Четыре Лапы» вы найдете все необходимое для организации питания и проживания своего питомца. Кошка не относится к числу прихотливых и слишком капризных домашних животных, но и для нее стоит покупать специальные средства. В интернет-магазине «Четыре Лапы» вы найдете все необходимое для организации питания и проживания своего питомца. Кошка не относится к числу прихотливых и слишком капризных домашних животных, но и для нее стоит покупать специальные средства.
		</div>
	</div>
 </dd>
</dl>
<dl class="b-adventure__item">
	<dt class="b-adventure__header-block"> <span class="b-icon b-icon--adventure"> </span>
	<h4 class="b-adventure__header">Условия доставки</h4>
 </dt>
	<dd class="b-adventure__description-block">
	<div class="b-adventure__text">
		 Курьерская доставка по Москве и области, а также в регионы России. Бесплатная доставка осуществляется бесплатно при оформлении заказа по Москве и области на сумму от 2000 рублей.
	</div>
	<div class="b-accordion">
 <a class="b-accordion__header js-toggle-accordion" href="javascript:void(0);" title="Подробнее о доставке">Подробнее о доставке</a>
		<div class="b-accordion__block js-dropdown-block">
			 Курьерская доставка по Москве и области, а также в регионы России. Бесплатная доставка осуществляется бесплатно при оформлении заказа по Москве и области на сумму от 2000 рублей. Курьерская доставка по Москве и области, а также в регионы России. Бесплатная доставка осуществляется бесплатно при оформлении заказа по Москве и области на сумму от 2000 рублей. Курьерская доставка по Москве и области, а также в регионы России. Бесплатная доставка осуществляется бесплатно при оформлении заказа по Москве и области на сумму от 2000 рублей. Курьерская доставка по Москве и области, а также в регионы России. Бесплатная доставка осуществляется бесплатно при оформлении заказа по Москве и области на сумму от 2000 рублей.
		</div>
	</div>
 </dd>
</dl>
<dl class="b-adventure__item">
	<dt class="b-adventure__header-block"> <span class="b-icon b-icon--adventure"> </span>
	<h4 class="b-adventure__header">Пять причин купить</h4>
 </dt>
	<dd class="b-adventure__description-block">
	<div class="b-adventure__text">
		 1. Мы гарантируем высокое качество продукции и предлагаем только оригинальные товары от производителей.
	</div>
	<div class="b-accordion">
 <a class="b-accordion__header js-toggle-accordion" href="javascript:void(0);" title="Все 5 причин">Все 5 причин</a>
		<div class="b-accordion__block js-dropdown-block">
			 2. Мы гарантируем высокое качество продукции и предлагаем только оригинальные товары от производителей.<br>
 <br>
			 3. Мы гарантируем высокое качество продукции и предлагаем только оригинальные товары от производителей.<br>
 <br>
			 4. Мы гарантируем высокое качество продукции и предлагаем только оригинальные товары от производителей.<br>
 <br>
			 5. Мы гарантируем высокое качество продукции и предлагаем только оригинальные товары от производителей.<br>
 <br>
		</div>
	</div>
 </dd>
</dl>
<dl class="b-adventure__item">
	<dt class="b-adventure__header-block"> <span class="b-icon b-icon--adventure"> </span>
	<h4 class="b-adventure__header">Специальные предложения</h4>
 </dt>
	<dd class="b-adventure__description-block">
	<div class="b-adventure__text">
		 Коты умеют отлично обустраивать свой быт и определять распорядок дня самостоятельно. Самостоятельные и не терпящие принуждений особи, на первый взгляд, вообще не нуждаются во внимании и заботе со стороны человека. Но это только на первый взгляд. Чтобы обеспечить нормальные условия жизни для кошки, придется регулярно выполнять важные процедуры по уходу и поддержанию здоровья питомца.
	</div>
	<div class="b-accordion">
 <a class="b-accordion__header js-toggle-accordion" href="javascript:void(0);" title="Подробнее о специальном предложении">Подробнее о специальном предложении</a>
		<div class="b-accordion__block js-dropdown-block">
			 Коты умеют отлично обустраивать свой быт и определять распорядок дня самостоятельно. Самостоятельные и не терпящие принуждений особи, на первый взгляд, вообще не нуждаются во внимании и заботе со стороны человека. Но это только на первый взгляд. Чтобы обеспечить нормальные условия жизни для кошки, придется регулярно выполнять важные процедуры по уходу и поддержанию здоровья питомца. Коты умеют отлично обустраивать свой быт и определять распорядок дня самостоятельно. Самостоятельные и не терпящие принуждений особи, на первый взгляд, вообще не нуждаются во внимании и заботе со стороны человека. Но это только на первый взгляд. Чтобы обеспечить нормальные условия жизни для кошки, придется регулярно выполнять важные процедуры по уходу и поддержанию здоровья питомца. Коты умеют отлично обустраивать свой быт и определять распорядок дня самостоятельно. Самостоятельные и не терпящие принуждений особи, на первый взгляд, вообще не нуждаются во внимании и заботе со стороны человека. Но это только на первый взгляд. Чтобы обеспечить нормальные условия жизни для кошки, придется регулярно выполнять важные процедуры по уходу и поддержанию здоровья питомца.
		</div>
	</div>
 </dd>
</dl><?php

/**
 * Закрываем контейнер.
 */
echo '</section>', '</div>';

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');

?>