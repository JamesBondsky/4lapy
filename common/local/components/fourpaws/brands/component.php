<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/**
 * Комплексный компонент раздела Бренды
 *
 * @updated: 21.12.2017
 */

$arParams['SEF_MODE'] = isset($arParams['SEF_MODE']) && $arParams['SEF_MODE'] === 'Y' ? 'Y' : 'N';

$sComponentPage = '';
if($arParams['SEF_MODE'] === 'Y') {
	$arDefaultUrlTemplates404 = array();
	$arComponentVariables = array();
	if($arParams['SEF_URL_TEMPLATES'] && is_array($arParams['SEF_URL_TEMPLATES'])) {
		foreach($arParams['SEF_URL_TEMPLATES'] as $sTmpPageName => $sTmpUrlTemplate) {
			if(!isset($arDefaultUrlTemplates404[$sTmpPageName])) {
				$arDefaultUrlTemplates404[$sTmpPageName] = $sTmpUrlTemplate;
				preg_match_all('~#([0-9a-zA-Z_-]+)#~', $sTmpUrlTemplate, $arMatches);
				if($arMatches && $arMatches[1]) {
					$arComponentVariables = array_merge($arComponentVariables, $arMatches[1]);
				}
			}
		}
		$arComponentVariables = array_unique($arComponentVariables);
	}

	$arDefaultVariableAliases404 = array();
	$arUrlTemplates = \CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams['SEF_URL_TEMPLATES']);

	$arParams['VARIABLE_ALIASES'] = isset($arParams['VARIABLE_ALIASES']) ? $arParams['VARIABLE_ALIASES'] : array();
	$arVariableAliases = \CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams['VARIABLE_ALIASES']);

	$sComponentPage = \CComponentEngine::ParseComponentPath(
		$arParams['SEF_FOLDER'],
		$arUrlTemplates,
		$arVariables
	);
	if(!$sComponentPage) {
		$sComponentPage = '404';
	}
	\CComponentEngine::InitComponentVariables($sComponentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	$arResult = array(
		'FOLDER' => $arParams['SEF_FOLDER'],
		'URL_TEMPLATES' => $arUrlTemplates,
		'VARIABLES' => $arVariables,
		'ALIASES' => $arVariableAliases,
		'PAGE' => $sComponentPage
	);
}

$this->IncludeComponentTemplate($sComponentPage);

return $arResult;
