<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/main/polyfill/complex/base-polyfill.js',
		'/bitrix/js/main/polyfill/complex/babel-external-helpers.js',
	],
	'rel' => [
		'main.polyfill.includes',
		'main.polyfill.fill',
		'main.polyfill.customevent'
	],
	'skip_core' => true,
];