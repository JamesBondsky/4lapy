<?
class basket extends \stdClass
{
	protected $items;
	protected $order_id;
	protected $fuser_id;

	function __construct($arParams)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		if ($arParams['order_id']) {
			$this->order_id = $arParams['order_id'];
		} elseif ($arParams['fuser_id']) {
			$this->fuser_id = $arParams['fuser_id'];
		}
	}

	public function getBasketItems()
	{
		if (is_null($this->items)) {
			$arFilter = array();

			if ($this->order_id) {
				$arFilter['=ORDER_ID'] = $this->order_id;
			} elseif ($this->fuser_id) {
				$arFilter['=FUSER_ID'] = $this->fuser_id;
				$arFilter['=ORDER_ID'] = null;
			}

			if (!empty($arFilter)) {
				$oBasketItems = \Bitrix\Sale\BasketTable::getList(array(
					'filter' => $arFilter
				));

				while ($arItem = $oBasketItems->fetch()) {
					$this->items[] = new \basket_item($arItem);
				}
			} else {
				$this->items = array();
			}
		}

		return $this->items;
	}

	public function deleteBasketItem(basket_item $oBasketItem)
	{
		foreach ($this->items as $key => $oItem) {
			if ($oItem == $oBasketItem) {
				unset($this->items[$key]);
			}
		}
	}

	public function getData()
	{
		$arResult = array();

		foreach ($this->getBasketItems() as $oItem) {
			$arResult[] = $oItem->getData();
		}

		return $arResult;
	}

	public function clear()
	{
		\Bitrix\Main\Loader::includeModule('sale');

		if ($this->fuser_id) {
			$oBaskets = \Bitrix\Sale\BasketTable::getList(array(
				'filter' => array(
					'=ORDER_ID' => null,
					'=FUSER_ID' => $this->fuser_id,
				),
				'select' => array('ID'),
			));

			while ($arBasket = $oBaskets->fetch()) {
				\CSaleBasket::Delete($arBasket['ID']);
			}
		}
	}
}