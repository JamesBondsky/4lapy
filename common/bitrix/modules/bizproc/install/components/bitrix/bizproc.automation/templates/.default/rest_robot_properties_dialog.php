<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$dialog = $arResult['dialog'];

$data = $dialog->getRuntimeData();
$map = $dialog->getMap();
$activityData = $data['ACTIVITY_DATA'];

$properties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : array();
$currentValues = $dialog->getCurrentValues();

foreach ($properties as $id => $property):
	$name = $map[$id]['FieldName'];
	$title = \Bitrix\Bizproc\RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID);
	?>
	<div class="bizproc-automation-popup-settings">
		<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete"><?=htmlspecialcharsbx($title)?>: </span>
		<?
		echo $dialog->renderFieldControl($map[$id], $currentValues[$name]);
		?>
	</div>
	<?
endforeach;