<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'/bitrix/js/ui/vue/components/smiles/ui.vue.components.smiles.bundle.js',
	],
	'css' => [
		'/bitrix/js/ui/vue/components/smiles/ui.vue.components.smiles.bundle.css',
	],
	'rel' => [
		'main.polyfill.complex',
		'ui.vue',
		'ui.vue.directives.lazyload',
		'ui.indexeddb',
		'rest.client',
	],
	'skip_core' => true,
];