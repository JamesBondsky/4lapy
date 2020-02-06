<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'js' => Array(
		'/bitrix/js/ui/vue/directives/lazyload/ui.vue.directives.lazyload.bundle.js',
	),
	'rel' => array(
		'main.polyfill.complex',
		'main.polyfill.intersectionobserver',
		'ui.vue',
	),
	'skip_core' => true,
	'bundle_js' => 'ui_vue'.(defined('VUEJS_DEBUG') && VUEJS_DEBUG? '_debug': '')
);