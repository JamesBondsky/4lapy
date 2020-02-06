<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'js' => array(
		'/bitrix/js/ui/vue/vuex/vuex.js',
		'/bitrix/js/ui/vue/vuex/ui.vue.vuex.bundle.js',
	),
	'rel' => array('ui.vue'),
	'skip_core' => true,
	'bundle_js' => 'ui_vue'.(defined('VUEJS_DEBUG') && VUEJS_DEBUG? '_debug': '')
);