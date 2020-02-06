<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Config\Option;

\Bitrix\Main\Loader::includeModule('ipol.dpd');

$ORDER_ID = $_REQUEST['ID'];
$arOrder  = \CSaleOrder::GetByID($ORDER_ID);

$deliveryCode = \Ipolh\DPD\Delivery\DPD::getDeliveryCode($arOrder['DELIVERY_ID']);
$profile      = \Ipolh\DPD\Delivery\DPD::getDeliveryProfile($deliveryCode);

$showButtonAlways = Option::get('ipol.dpd', 'SHOW_ADMIN_BUTTON', '') == 'ALWAYS';

if ($profile !== false || $showButtonAlways) {
	$entity = \Ipolh\DPD\DB\Order\Table::findByOrder($ORDER_ID, true);
	
	if (!$entity->id) {
		$errors = $entity->save();
	}

	if (!$entity->isCreated()) {
		$entity->fillFromOrder($ORDER_ID);
	}

	$form = new \Ipolh\DPD\Admin\Order\Edit($entity);
	$form->processAndShow();
}