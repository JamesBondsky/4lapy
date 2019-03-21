<?
use FourPaws\App\Application;
use FourPaws\EcommerceBundle\Preset\Bitrix\MapperPreset;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\UserBundle\Service\UserCitySelectInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 */

if (empty($arResult['ITEMS']) || !\is_array($arResult['ITEMS'])) {
    return;
}

$container = Application::getInstance()->getContainer();
$ecommerceService = $container->get(GoogleEcommerceService::class);
$mapper = $container->get(MapperPreset::class)->mapperSliderFactory();

/*$arResult['ECOMMERCE_VIEW_SCRIPT'] = $ecommerceService->renderScript(
    $ecommerceService->buildPromotionFromArray($mapper, $arResult['ITEMS'], 'promoView'), true
);*/

foreach ($arResult['ITEMS'] as &$item)
{
    /*$item['ECOMMERCE_CLICK_SCRIPT'] = $ecommerceService->renderScript(
        $ecommerceService->buildPromotionFromArray($mapper, [$item], 'promoClick', 'promotionClick'), false
    );*/

    $additionalClasses = [];
    if ($item['DISPLAY_PROPERTIES']['COLOR']['VALUE_XML_ID'] === 'dark')
    {
        $additionalClasses[] = 'b-promo-banner-item--dark';
    }
    if ($item['DISPLAY_PROPERTIES']['BIG_TEXT']['VALUE'] === true)
    {
        $additionalClasses[] = 'b-promo-banner-item--big-text';
    }

    $showItem = true;
    if ($item['DISPLAY_PROPERTIES']['LOCATION']['VALUE'] && $arParams['SELECTED_CITY_CODE'] && !in_array($arParams['SELECTED_CITY_CODE'], $item['DISPLAY_PROPERTIES']['LOCATION']['VALUE'], true)) {
        $showItem = false;
    }

    $item['MOD']['ADDITIONAL_CLASSES'] = ' ' . implode(' ', $additionalClasses);
    $item['MOD']['SHOW_ITEM'] = $showItem;
}
