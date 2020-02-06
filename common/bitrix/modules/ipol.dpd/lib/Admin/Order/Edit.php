<?php
namespace Ipolh\DPD\Admin\Order;

use \Bitrix\Main\Localization\Loc;


use \Ipolh\DPD\Admin\Form\AbstractForm;
use \Ipolh\DPD\DB\Order\Table as OrderTable;
use \Ipolh\DPD\DB\Order\Model as OrderModel;
use \Ipolh\DPD\DB\Terminal\Table as TerminalTable;
use \Ipolh\DPD\DB\Terminal\Model as TerminalModel;


use \Ipolh\DPD\API\User as API;
use \Ipolh\DPD\Shipment;

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/'. IPOLH_DPD_MODULE .'/options.php');


class Edit extends AbstractForm
{
	protected $itemId;

	protected $formName = 'IPOLH_DPD_ORDER';

	/**
	 * Конструктор класса
	 * @param int $orderId id заказа
	 */
	public function __construct($itemId)
	{
		if ($itemId instanceof OrderModel) {
			$this->itemId = $itemId->id;
			$this->editItem = $itemId;
		} else {
			$this->itemId = $itemId;
			$this->loadItem();
		}

		$this->actionUrl = '/bitrix/admin/ipolh_dpd_order_edit.php?ID='. $this->getEditItem()->orderId;
	}

	/**
	 * Загружаем из БД
	 *
	 * @return void
	 */
	public function loadItem()
	{
		$this->editItem = OrderTable::findByOrder($this->orderId);
	}

	/**
	 * Сохраняем в БД
	 *
	 * @return void
	 */
	public function saveItem()
	{
		$result = new \Bitrix\Main\Result();

		$order = $this->getEditItem();
		$shipment = $order->getShipment(true);

		switch ($_REQUEST['IPOLH_DPD_ACTION']) {
			case 'CREATE_ORDER':
				$result = $order->dpd()->create();
				$message = Loc::getMessage('IPOLH_DPD_ORDER_CREATED');
			break;

			case 'CANCEL_ORDER':
				$result = $order->dpd()->cancel();
				$message = Loc::getMessage('IPOLH_DPD_ORDER_CANCELED');
			break;

			case 'LABEL_FILE':
				$count      = $_REQUEST['IPOLH_DPD_ORDER']['LABEL_FILE_COUNT'];
				$fileFormat = $_REQUEST['IPOLH_DPD_ORDER']['LABEL_FILE_FORMAT'];
				$pageSize   = $_REQUEST['IPOLH_DPD_ORDER']['LABEL_FILE_SIZE'];

				$result = $order->dpd()->getLabelFile($count, $fileFormat, $pageSize);
				$message = Loc::getMessage('IPOLH_DPD_ORDER_LABEL_FILE_OK', array('#LINK#' => $this->getRenderer()->renderFormFieldLink('LABEL_FILE', array())));
			break;

			case 'INVOICE_FILE':
				$result = $order->dpd()->getInvoiceFile();
				$message = Loc::getMessage('IPOLH_DPD_ORDER_INVOICE_FILE_OK', array('#LINK#' => $this->getRenderer()->renderFormFieldLink('INVOICE_FILE', array())));
			break;

			case 'RECALCULATE':
				$result = $order->getTariffDelivery();
				$message = Loc::getMessage('IPOLH_DPD_ORDER_RECALCULATE', array(
					'#PRICE_ACTUAL#'   => \SaleFormatCurrency($order->actualPriceDelivery, $order->currency),
					'#PRICE_DELIVERY#' => \SaleFormatCurrency($order->priceDelivery, $order->currency),
				));
			break;

			default:
				$result->addError(\Bitrix\Main\Error(Loc::getMessage('IPOLH_DPD_ORDER_UNKNOWN_ACTION')));
		}

		if ($result->isSuccess()) {
			$result->setData(array('message' => $message));
		}

		return $result;
	}

	/**
	 * Отрисовывает форму
	 *
	 * @param  \Bitrix\Main\Result  $result
	 * @return string
	 */
	public function render(\Bitrix\Main\Result $result = null)
	{
		\CJSCore::Init('ipolh_dpd_admin_order_detail');

		return ''
			. '<div id="IPOLH_DPD_ORDER_FORM" style="margin: -12px"'. $this->getOrderJsDataAttrs() .'>'
			. '		<style>#IPOLH_DPD_ORDER_FORM .adm-detail-content {padding: 12px;}</style>'
			. 		parent::render($result)
			.       $this->renderSummaryInfo()
			. '</div>'
		;
	}

	protected function getOrderJsDataAttrs()
	{
		$attrs = array(
			'order-id'     => $this->getEditItem()->id,
			'order-num'    => $this->getEditItem()->orderNum,
			'order-status' => $this->getEditItem()->orderStatus,
		);

		$ret = '';
		foreach ($attrs as $name => $value) {
			$ret .= ' data-'. $name .'="'. $value .'"';
		}

		return $ret;
	}

	protected function renderSummaryInfo()
	{
		$order = $this->getEditItem();

		return ''
			. '<div class="adm-s-result-container" style="overflow: hidden">'
			. '	<div class="adm-s-result-container-itog">'
			. '		<table class="adm-s-result-container-itog-table">'
			. '			<tr>'
			. '				<td>'. Loc::getMessage('IPOLH_DPD_ORDER_GOODS_PRICE') .'</td>'
			. '				<td>'. \SaleFormatCurrency($order->price, $order->currency) .'</td>'
			. '			</tr>'
			. '			<tr>'
			. '				<td>'. Loc::getMessage('IPOLH_DPD_ORDER_DELIVERY_PRICE') .'</td>'
			. '				<td>'. \SaleFormatCurrency($order->priceDelivery, $order->currency) .'</td>'
			. '			</tr>'
			. '			<tr>'
			. '				<td>'. Loc::getMessage('IPOLH_DPD_ORDER_TARIFF_PRICE') .'</td>'
			. '				<td>'. ($order->actualPriceDelivery === false
										? Loc::getMessage('IPOLH_DPD_ORDER_TARIFF_PRICE_ERROR')
										: \SaleFormatCurrency($order->actualPriceDelivery, $order->currency))
			. '				</td>'
			. '			</tr>'
			. '			<tr class="adm-s-result-container-itog-table-result">'
			. '				<td style="background: #dbe3b9; font-weight: bold;">'. Loc::getMessage('IPOLH_DPD_ORDER_PAYED_PRICE') .'</td>'
			. '				<td style="background: #dbe3b9; font-weight: bold;">'. \SaleFormatCurrency($order->payedPrice, $order->currency) .'</td>'
			. '			</tr>'
			. '			<tr class="adm-s-result-container-itog-table-result">'
			. '				<td style="background: #dbe3b9; font-weight: bold;">'. Loc::getMessage('IPOLH_DPD_ORDER_CARGO_VALUE') .'</td>'
			. '				<td style="background: #dbe3b9; font-weight: bold;">'. \SaleFormatCurrency($order->cargoValue, $order->currency) .'</td>'
			. '			</tr>'
			. '			<tr class="adm-s-result-container-itog-table-result">'
			. '				<td style="background: #dbe3b9; font-weight: bold;">'. Loc::getMessage('IPOLH_DPD_ORDER_NPP') .'</td>'
			. '				<td style="background: #dbe3b9; font-weight: bold;">'. \SaleFormatCurrency($order->sumNpp, $order->currency) .'</td>'
			. '			</tr>'
			. '		</table>'
			. '	</div>'
			.'</div>'
		;
	}

	/**
	 * Поля формы
	 *
	 * @return array
	 */
	public function getFields()
	{
		$order    = $this->getEditItem();
		$disabled = $order->isCreated();

		$senderDimensionsError = '';
		$receiverDimensionsError = '';
		$receiverNppError = '';

		if ($order->isSelfPickup()) {
			$terminal = TerminalModel::getByCode($order->senderTerminalCode);

			if ($terminal && !$terminal->checkShipmentDimessions($order->getShipment())) {
				$senderDimensionsError .= Loc::getMessage('IPOLH_DPD_ORDER_SENDER_DIMENSIONS_ERROR');
			}
		}

		if ($order->isSelfDelivery()) {
			$terminal = TerminalModel::getByCode($order->receiverTerminalCode);
			
			if ($terminal) {
				if (!$terminal->checkShipmentDimessions($order->getShipment())) {
					$receiverDimensionsError .= Loc::getMessage('IPOLH_DPD_ORDER_RECEIVER_DIMENSIONS_ERROR');
				}

				if ($order->npp == 'Y' && !$terminal->checkShipmentPayment($order->getShipment())) {
					$receiverNppError .= Loc::getMessage('IPOLH_DPD_ORDER_NPP_ERROR');
				}
			}
		}

		return array(
			array(
				'DIV'      => 'IPOLH_DPD_ORDER_TAB_1',
				'TAB'      => Loc::getMessage('IPOLH_DPD_ORDER_TAB_1'),
				'ICON'     => '',
				'TITLE'    => Loc::getMessage('IPOLH_DPD_ORDER_TAB_1_TITLE'),
				'HELP'     => $order->isNew() ? '' : Loc::getMessage('IPOLH_DPD_ORDER_EDIT_NOTE'),
				'OPTIONS'  => array(),
				'CONTROLS' => array(
					'ORDER_STATUS' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_STATUS'),
						'TYPE' => 'NOTE',
						'COMMENT' => $order->orderStatusText,
					),

					'ORDER_ID' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_ID'),
						'TYPE'  => 'NOTE',
					),

					'ORDER_NUM' => array(
						'TITLE'   => Loc::getMessage('IPOLH_DPD_ORDER_NUM'),
						'TYPE'    => 'NOTE',
					),

					'PAYMENT_TYPE' => array(
						'TITLE'   => Loc::getMessage('IPOLH_DPD_PAYMENT_TYPE'),
						'TYPE'    => 'SELECT',
						'ITEMS'   => array_merge(
							array(
								''    => Loc::getMessage("IPOLH_DPD_PAYMENT_TYPE_AUTO"),
								'OUP' => Loc::getMessage("IPOLH_DPD_PAYMENT_TYPE_OUP"),
							),
							
							!$order->isSelfPickup() ? array() : array(
								'OUO' => Loc::getMessage("IPOLH_DPD_PAYMENT_TYPE_OUO"),
							)
						),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'SERVICE_CODE' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_SERVICE_CODE'),
						'TYPE'  => 'SELECT',
						'ITEMS' => \Ipolh\DPD\Calculator::AllowedTariffList(),
						'NULL'  => Loc::getMessage('IPOLH_DPD_ORDER_LIST_NULL'),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'SERVICE_VARIANT' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_SERVICE_VARIANT'),
						'TYPE'  => 'SELECT',
						'ITEMS' => array(
							Loc::getMessage('IPOLH_DPD_ORDER_SERVICE_VARIANT_DD') => Loc::getMessage('IPOLH_DPD_ORDER_SERVICE_VARIANT_DD_TITLE'),
							Loc::getMessage('IPOLH_DPD_ORDER_SERVICE_VARIANT_DT') => Loc::getMessage('IPOLH_DPD_ORDER_SERVICE_VARIANT_DT_TITLE'),
							Loc::getMessage('IPOLH_DPD_ORDER_SERVICE_VARIANT_TD') => Loc::getMessage('IPOLH_DPD_ORDER_SERVICE_VARIANT_TD_TITLE'),
							Loc::getMessage('IPOLH_DPD_ORDER_SERVICE_VARIANT_TT') => Loc::getMessage('IPOLH_DPD_ORDER_SERVICE_VARIANT_TT_TITLE'),
						),
						'NULL'  => Loc::getMessage('IPOLH_DPD_ORDER_LIST_NULL'),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'PICKUP_DATE' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_PICKUP_DATE'),
						'TYPE'  => 'DATE',
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'PICKUP_TIME_PERIOD' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_PICKUP_TIME_PERIOD'),
						'TYPE'  => 'SELECT',
						'ITEMS' => array(
							'9-18'  => Loc::getMessage('IPOLH_DPD_OPTIONS_PICKUP_TIME_PERIOD_9_18'),
							'9-13'  => Loc::getMessage('IPOLH_DPD_OPTIONS_PICKUP_TIME_PERIOD_9_13'),
							'13-18' => Loc::getMessage('IPOLH_DPD_OPTIONS_PICKUP_TIME_PERIOD_13_18'),
						),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'DELIVERY_TIME_PERIOD' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_DELIVERY_TIME_PERIOD'),
						'TYPE'  => 'SELECT',
						'ITEMS' => array(
							'9-18'  => Loc::getMessage('IPOLH_DPD_OPTIONS_DELIVERY_TIME_PERIOD_9_18'),
							'9-13'  => Loc::getMessage('IPOLH_DPD_OPTIONS_DELIVERY_TIME_PERIOD_9_13'),
							'13-18' => Loc::getMessage('IPOLH_DPD_OPTIONS_DELIVERY_TIME_PERIOD_13_18'),
							'18-21' => Loc::getMessage('IPOLH_DPD_OPTIONS_DELIVERY_TIME_PERIOD_18_21'),
						),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'SUBHEADER_ORDER_INFO' => array(
						'TYPE'  => 'HEADER',
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_INFO'),

					),

					'CARGO_WEIGHT' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_CARGO_WEIGHT'),
						'TYPE'  => 'STRING',
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'DIMENSION' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_DIMENSIONS'),
						'TYPE'  => 'CONTROL_GROUP',
						'SPLIT' => ' x ',
						'ITEMS' => array(
							'DIMENSION_WIDTH'  => array('TYPE' => 'STRING'),
							'DIMENSION_HEIGHT' => array('TYPE' => 'STRING'),
							'DIMENSION_LENGTH' => array('TYPE' => 'STRING'),
						),
						'ATTRS' => array(
							'disabled' => $disabled,
							'style'    => 'width: 40px; text-align: center',
							'onkeyup'  => "
								BX('IPOLH_DPD_ORDER_CARGO_VOLUME').value = (
									  (parseFloat(BX('IPOLH_DPD_ORDER_DIMENSION_WIDTH').value))
									* (parseFloat(BX('IPOLH_DPD_ORDER_DIMENSION_HEIGHT').value))
									* (parseFloat(BX('IPOLH_DPD_ORDER_DIMENSION_LENGTH').value))
								/ 1000000).toFixed(6)
							",
						),
						'COMMENT' => ''
							. $senderDimensionsError
							. ($senderDimensionsError && $receiverDimensionsError ? '<br>' : '')
							. $receiverDimensionsError,
					),

					'CARGO_VOLUME' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_CARGO_VOLUME'),
						'TYPE'  => 'STRING',
						'ATTRS' => array(
							'readonly' => true,
						),
					),

					'CARGO_NUM_PACK' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_CARGO_NUM_PACK'),
						'TYPE'  => 'STRING',
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'CARGO_CATEGORY' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_CARGO_CATEGORY'),
						'TYPE'  => 'STRING',
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),
				),
			),

			array(
				'DIV'      => 'IPOLH_DPD_OPTIONS_TAB_SENDER',
				'TAB'      => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_SENDER'),
				'ICON'     => '',
				'TITLE'    => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_SENDER_TITLE'),
				'OPTIONS'  => array(),
				'CONTROLS' => array_merge(
					array(
						'SENDER_FIO' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_FIO'),
							'HELP'  => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_FIO_HELP'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_NAME' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_NAME'),
							'HELP'  => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_NAME_HELP'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_PHONE' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_PHONE'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_SUBHEADER_ADDRESS' => array(
							'TYPE'  => 'HEADER',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_ADDRESS_SUBHEADER'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_LOCATION_TEXT' => array(
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_LOCATION'),
							'TYPE'  => function() use ($order) {
								return $order->getSenderLocationText();
							},
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),
					),

					$order->getShipment()->getSelfPickup() ? array(
						'SENDER_TERMINAL_CODE' => array(
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_TERMINAL_CODE'),
							'TYPE'  => 'SELECT',
							'ITEMS' => function() use ($order) {
								$terminals = TerminalModel::getList([
									'filter' => [
										'LOCATION_ID'           => $order->getShipment()->getSender()['ID'],
										'!SCHEDULE_SELF_PICKUP' => false,
									],
									'order' => ['NAME' => 'ASC'],
								]);

								$ret = [];
								foreach ($terminals as $terminal) {
									if ($terminal->checkShipmentDimessions($order->getShipment())) {
										$ret[$terminal['ID']] = $terminal['NAME'];
									}
								}

								return $ret;
							},
							'COMMENT' => $senderDimensionsError,
							'ATTRS' => array(
								'disabled' => $disabled
							),
							'NULL' => '',
						),
					) : array(
						'SENDER_STREET' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_STREET'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_STREETABBR' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_STREETABBR'),
							'HELP'  => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_STREETABBR_HELP'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_HOUSE' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_HOUSE'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_KORPUS' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_KORPUS'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_STR' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_STR'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_VLAD' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_VLAD'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_OFFICE' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_OFFICE'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'SENDER_FLAT' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_FLAT'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),
					)
				),
			),

			array(
				'DIV'      => 'IPOLH_DPD_OPTIONS_TAB_RECEIVER',
				'TAB'      => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_RECEIVER'),
				'ICON'     => '',
				'TITLE'    => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_RECEIVER_TITLE'),
				'OPTIONS'  => array(),
				'CONTROLS' => array_merge(
					array(
						'RECEIVER_FIO' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_FIO'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_NAME' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_NAME'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_PHONE' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_PHONE'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_SUBHEADER_ADDRESS' => array(
							'TYPE'  => 'HEADER',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_ADDRESS_SUBHEADER'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_LOCATION_TEXT' => array(
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_LOCATION'),
							'TYPE'  => function() use ($order) {
								return $order->getReceiverLocationText();
							},
						),
					),

					$order->getShipment()->getSelfDelivery() ? array(
						'RECEIVER_TERMINAL_CODE' => array(
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_TERMINAL_CODE'),
							'TYPE'  => 'SELECT',
							'ITEMS' => function() use ($order) {
								$terminals = TerminalModel::getList([
									'filter' => [
										'LOCATION_ID'             => $order->getShipment()->getReceiver()['ID'],
										'!SCHEDULE_SELF_DELIVERY' => false,
									],
									'order' => ['NAME' => 'ASC'],
								]);

								$ret = [];
								foreach ($terminals as $terminal) {
									if ($terminal->checkShipmentDimessions($order->getShipment())
										&& ($order->npp != 'Y' || $terminal->checkShipmentPayment($order->getShipment()))
									) {
										$ret[$terminal['ID']] = $terminal['NAME'];
									}
								}

								return $ret;
							},
							'ATTRS' => array(
								'disabled' => $disabled
							),
							'COMMENT' => $receiverDimensionsError .' '. $receiverNppError,
							'NULL' => '',
						),

						'RECEIVER_COMMENT' => array(
							'TYPE' => 'STRING',
							'MULTILINE' => true,
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_COMMENT'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),
					) : array(
						'RECEIVER_STREET' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_STREET'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_STREETABBR' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_STREETABBR'),
							'HELP'  => Loc::getMessage('IPOLH_DPD_OPTIONS_SENDER_STREETABBR_HELP'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_HOUSE' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_HOUSE'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_KORPUS' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_KORPUS'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_STR' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_STR'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_VLAD' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_VLAD'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_OFFICE' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_OFFICE'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_FLAT' => array(
							'TYPE'  => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_FLAT'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
						),

						'RECEIVER_COMMENT' => array(
							'TYPE' => 'STRING',
							'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_RECEIVER_COMMENT'),
							'ATTRS' => array(
								'disabled' => $disabled
							),
							'MULTILINE' => true,
						),
					)
				),
			),

			array(
				'DIV'      => 'IPOLH_DPD_ORDER_TAB_PAYMENT',
				'TAB'      => Loc::getMessage('IPOLH_DPD_ORDER_TAB_PAYMENT'),
				'ICON'     => '',
				'TITLE'    => Loc::getMessage('IPOLH_DPD_ORDER_TAB_PAYMENT_TITLE'),
				'HELP'     => Loc::getMessage('IPOLH_DPD_ORDER_TAB_PAYMENT_HELP'),
				'OPTIONS'  => array(),
				'CONTROLS' => array(
					'SUBHEADER_PAYMENT_CARGO' => array(
						'TYPE'  => 'HEADER',
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_SUBHEADER_PAYMENT_CARGO'),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'CARGO_VALUE' => array(
						'TYPE'  => 'STRING',
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_CARGO_VALUE', ['#CURRENCY#' => $this->editItem->currency]),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'SUBHEADER_PAYMENT_NPP' => array(
						'TYPE'  => 'HEADER',
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_NPP'),
						'ATTRS' => array(
							'disabled' => $disabled,
						),
					),

					'NPP' => array(
						'TYPE'          => 'CHECKBOX',
						'TITLE'         => Loc::getMessage('IPOLH_DPD_ORDER_NPP_CHECK'),
						'VALUE'         => 'Y',
						'UNCHECK_VALUE' => 'N',
						'ATTRS' => array(
							'disabled' => $disabled,
						),
						'COMMENT' => $receiverNppError
					),

					'SUM_NPP' => array(
						'TYPE'    => 'STRING',
						'TITLE'   => Loc::getMessage('IPOLH_DPD_ORDER_SUM_NPP', ['#CURRENCY#' => $this->editItem->currency]),
						'COMMENT' => $order->payedPrice > 0
										? Loc::getMessage('IPOLH_DPD_ORDER_SUM_NPP_COMMENT', array('SUM' => \SaleFormatCurrency($order->payedPrice, $order->currency)))
										: '',
						'ATTRS' => array(
							'disabled' => $disabled,
						),
					),
				),
			),

			array(
				'DIV'      => 'IPOLH_DPD_ORDER_TAB_SERVICES',
				'TAB'      => Loc::getMessage('IPOLH_DPD_ORDER_TAB_SERVICES'),
				'ICON'     => '',
				'TITLE'    => Loc::getMessage('IPOLH_DPD_ORDER_TAB_SERVICES_TITLE'),
				'HELP'     => Loc::getMessage('IPOLH_DPD_ORDER_TAB_SERVICES_HELP'),
				'OPTIONS'  => array(),
				'CONTROLS' => array(
					'CARGO_REGISTERED' => array(
						'TYPE'  => 'CHECKBOX',
						'VALUE' => 'Y',
						'UNCHECK_VALUE' => 'N',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_CARGO_REGISTERED'),
						'HELP'  => Loc::getMessage('IPOLH_DPD_OPTIONS_CARGO_REGISTERED_HELP'),
						'ATTRS' => array(
							'disabled' => $disabled,
						),
					),

					'DVD' => array(
						'TYPE'  => 'CHECKBOX',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_DVD'),
						'HELP'    => Loc::getMessage('IPOLH_DPD_OPTIONS_DVD_HELP'),
						'COMMENT' => Loc::getMessage('IPOLH_DPD_OPTIONS_PAID_COMMENT'),
						'ATTRS' => array(
							'disabled' => $disabled,
						),
						'VALUE' => 'Y',
						'UNCHECK_VALUE' => 'N',
					),

					'TRM' => array(
						'TYPE'  => 'CHECKBOX',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_TRM'),
						'COMMENT' => Loc::getMessage('IPOLH_DPD_OPTIONS_PAID_COMMENT'),
						'ATTRS' => array(
							'disabled' => $disabled,
						),
						'VALUE' => 'Y',
						'UNCHECK_VALUE' => 'N',
					),

					'PRD' => array(
						'TYPE'  => 'CHECKBOX',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_PRD'),
						'HELP'  => Loc::getMessage('IPOLH_DPD_OPTIONS_PRD_HELP'),
						'COMMENT' => Loc::getMessage('IPOLH_DPD_OPTIONS_PAID_COMMENT'),
						'ATTRS' => array(
							'disabled' => $disabled
						),
						'VALUE' => 'Y',
						'UNCHECK_VALUE' => 'N',
					),

					'VDO' => array(
						'TYPE'  => 'CHECKBOX',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_VDO'),
						'HELP'  => Loc::getMessage('IPOLH_DPD_OPTIONS_VDO_HELP'),
						'COMMENT' => Loc::getMessage('IPOLH_DPD_OPTIONS_PAID_COMMENT'),
						'ATTRS' => array(
							'disabled' => $disabled
						),
						'VALUE' => 'Y',
						'UNCHECK_VALUE' => 'N',
					),

					'OGD' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_OGD'),
						'COMMENT' => Loc::getMessage('IPOLH_DPD_OPTIONS_PAID_COMMENT'),
						'TYPE'  => 'SELECT',
						'ITEMS' => array(
							''     => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_EMPTY"),
							Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_VNESH") => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_VNESH_TITLE"),
							Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_PRIM") => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_PRIM_TITLE"),
							Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_RAB") => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_RAB_TITLE"),
							Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_SOOT") => Loc::getMessage("IPOLH_DPD_OPTIONS_OGD_SOOT_TITLE"),
						),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'SUBHEADER_OPTIONS_NOTIFY' => array(
						'TYPE'  => 'HEADER',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_TAB_OPTIONS_SUBTAB_NOTIFY'),
						'ATTRS' => array(
							'disabled' => $disabled
						),

					),

					'SMS' => array(
						'TYPE'  => 'HIDDEN',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_SMS'),
						'HELP'  => Loc::getMessage('IPOLH_DPD_OPTIONS_SMS_HELP'),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'EML' => array(
						'TYPE'  => 'HIDDEN',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_EML'),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'ESD' => array(
						'TYPE'  => 'HIDDEN',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_ESD'),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'ESZ' => array(
						'TYPE'  => 'STRING',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_ESZ'),
						'HELP'  => 'E-mail',
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),

					'POD' => array(
						'TYPE'  => 'HIDDEN',
						'TITLE' => Loc::getMessage('IPOLH_DPD_OPTIONS_POD'),
						'HELP'  => Loc::getMessage('IPOLH_DPD_OPTIONS_POD_HELP'),
						'COMMENT' => Loc::getMessage('IPOLH_DPD_OPTIONS_PAID_COMMENT'),
						'ATTRS' => array(
							'disabled' => $disabled
						),
					),
				),
			),

			array(
				'DIV'      => 'IPOLH_DPD_ORDER_TAB_DOCUMENTS',
				'TAB'      => Loc::getMessage('IPOLH_DPD_ORDER_DOCUMENTS'),
				'ICON'     => '',
				'TITLE'    => Loc::getMessage('IPOLH_DPD_ORDER_DOCUMENTS_TITLE'),
				'OPTIONS'  => array(),
				'HELP'     => $order->isDpdCreated() ? '' : Loc::getMessage('IPOLH_DPD_ORDER_DOCUMENTS_HELP'),
				'CONTROLS' => $order->isDpdCreated() ? array(
					'SUBHEADER_DOCUMENTS_INVOICE' => array(
						'TYPE'  => 'HEADER',
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_SUBHEADER_DOCUMENTS_INVOICE'),
					),

					'INVOICE_FILE' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_INVOICE_FILE'),
						'TYPE'  => empty($order->invoiceFile) ? 'HIDDEN' : 'LINK',
					),

					'BUTTON_INVOICE_FILE' => array(
						'TITLE' => '',
						'TYPE'  => 'BUTTON',
						'ATTRS' => array(
							'value' => empty($order->invoiceFile) ? Loc::getMessage('IPOLH_DPD_ORDER_BUTTON_INVOICE_1') : Loc::getMessage('IPOLH_DPD_ORDER_BUTTON_INVOICE_2'),
						),
					),

					'SUBHEADER_DOCUMENTS_STICKER' => array(
						'TYPE'  => 'HEADER',
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_SUBHEADER_DOCUMENTS_STICKER'),
					),

					'LABEL_FILE' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_LABEL_FILE'),
						'TYPE'  => empty($order->labelFile) ? 'HIDDEN' : 'LINK',
					),

					'LABEL_FILE_COUNT' => array(
						'TITLE'   => Loc::getMessage('IPOLH_DPD_ODER_LABEL_FILE_COUNT'),
						'TYPE'    => 'STRING',
						'DEFAULT' => $order->CARGO_NUM_PACK ?: 1,
					),

					'LABEL_FILE_FORMAT' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_LABEL_FILE_FORMAT'),
						'TYPE'  => 'SELECT',
						'ITEMS' => array(
							'PDF' => 'PDF',
							'FP3' => 'FP3',
						),
					),

					'LABEL_FILE_SIZE' => array(
						'TITLE' => Loc::getMessage('IPOLH_DPD_ORDER_LABEL_FILE_SIZE'),
						'TYPE'  => 'SELECT',
						'ITEMS' => array(
							'A5' => 'A5',
							'A6' => 'A6',
						),
					),

					'BUTTON_LABEL_FILE' => array(
						'TITLE' => '',
						'TYPE'  => 'BUTTON',
						'ATTRS' => array(
							'value' => empty($order->labelFile) ? Loc::getMessage('IPOLH_DPD_ORDER_BUTTON_LABEL_1') : Loc::getMessage('IPOLH_DPD_ORDER_BUTTON_LABEL_2'),
						),
					),
				) : array(),
			),
		);
	}
}