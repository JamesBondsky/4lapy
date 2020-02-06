<?php
namespace Ipolh\DPD;

use \Bitrix\Main\SystemException;
use \Ipolh\DPD\Delivery\DPD;

class EventListener
{
	/**
	 * Сохраняем данные о доставке в заказе
	 *
	 * @param  int   $orderId
	 * @param  array $arOrder
	 * @return void
	 */
	public static function saveDeliveryInfo($orderId, $arOrder)
	{
		$deliveryCode = DPD::getDeliveryCode($arOrder['DELIVERY_ID']);
		$profile = DPD::getDeliveryProfile($deliveryCode);
		if ($profile === false) {
			return;
		}

		$entity = \Ipolh\DPD\DB\Order\Table::findByOrder($orderId, true);
		if ($entity->id) {
			return;
		}

		$_REQUEST['IPOLH_DPD_ORDER']  = $_REQUEST['IPOLH_DPD_ORDER']  ?: $_SESSION['IPOLH_DPD_ORDER'];
		$_REQUEST['IPOLH_DPD_TARIFF'] = $_REQUEST['IPOLH_DPD_TARIFF'] ?: $_SESSION['IPOLH_DPD_TARIFF'];

		$entity->serviceCode = $_REQUEST['IPOLH_DPD_TARIFF'][$profile];
		$entity->serviceVariant = $profile;
		$entity->receiverTerminalCode = $_REQUEST['IPOLH_DPD_TERMINAL'][$profile] ?: null;

		$entity->save();
	}

	/**
	 * Отрисовывает форму редактирования заказа
	 */
	public static function showAdminForm()
	{
		$userRights = \CMain::GetUserRight('sale');
		$depths = array('D' => 1, 'U' => 2, 'W' => 3);

		if ($depths['U'] > $depths[$userRights]) {
			return;
		}

		if (strpos($_SERVER['PHP_SELF'], "/bitrix/admin/sale_order_detail.php") !== false
			|| strpos($_SERVER['PHP_SELF'], "/bitrix/admin/sale_order_view.php") !== false
		) {
			require($_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/'. IPOLH_DPD_MODULE .'/admin/order_edit.php');
		}
	}
}