<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

class PixelVk extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_PIXEL_VK_USE')
			)),
			'COUNTER' => new Field\Text('COUNTER', array(
				'title' => Loc::getMessage('LANDING_HOOK_PIXEL_VK_COUNTER'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_PIXEL_VK_PLACEHOLDER2')
			))
		);
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return $this->fields['USE']->getValue() == 'Y';
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		$counter = \htmlspecialcharsbx(trim($this->fields['COUNTER']));
		$counter = \CUtil::jsEscape($counter);
		if ($counter)
		{
			Manager::setPageView(
				'AfterBodyOpen',
				'<div id="vk_api_transport"></div>
<script data-skip-moving="true">
var pixel;
window.vkAsyncInit = function() {
	pixel = new VK.Pixel("' . $counter . '");
};
setTimeout(function() {
	if (window.VK) {return;}
	var el = document.createElement("script");
	el.type = "text/javascript";
	el.src = "https://vk.com/js/api/openapi.js?159";
	el.async = true;
	document.getElementById("vk_api_transport").appendChild(el);
}, 0);
</script>'
			);
		}
	}
}
