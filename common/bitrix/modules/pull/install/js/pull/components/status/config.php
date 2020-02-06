<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'/bitrix/js/pull/components/status/pull.components.status.bundle.js',
	],
	'css' => [
		'/bitrix/js/pull/components/status/pull.components.status.bundle.css',
	],
	'rel' => [
		'main.polyfill.complex',
		'ui.vue',
		'pull.client',
	],
	'skip_core' => true,
];