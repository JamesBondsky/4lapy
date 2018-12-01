<?
class basket_item extends \APIServer
{
	private $fields;

	function __construct($argument)
	{
		if (is_array($argument)) {
			$this->fields = $argument;
		}
	}

	public function getField($fieldName)
	{
		return $this->fields[$fieldName];
	}

	public function setField($fieldName, $fieldValue)
	{
		if ($fieldName == 'USER_ID') {
			$this->user = new \user($fieldValue);
		}

		$this->fields[$fieldName] = $fieldValue;
	}

	public function setFields($arFieldsValue)
	{
		foreach ($arFieldsValue as $fieldName => $fieldValue) {
			$this->setField($fieldName, $fieldValue);
		}
	}

	public function getData()
	{
		$arResult = null;

		//получаем основную инфу по товарам
		$oGoodsList = new \goods_list;
		$oGoodsList->User = $this->user;
		$arProdInfo = $oGoodsList->GetProdInfo($this->fields['PRODUCT_ID']);
		$arProdInfo = reset($arProdInfo);

		if ($arProdInfo) {
			//формируем стоимость позиции
			$arProdInfo['price'] = array(
				'actual' => $this->fields['PRICE'],
				'old' => ($this->fields['DISCOUNT_PRICE'] > 0 ? $this->fields['DISCOUNT_PRICE'] + $this->fields['PRICE'] : '')
			);

			//получаем количество бонусов по позиции
			$arProductBonus = $oGoodsList->GetProductBonus($arProdInfo['price'],$arProdInfo);

			//округляем хз как
			$arProdInfo['bonus_user'] = $arProductBonus['bonus_user'];
			$arProdInfo['bonus_all'] = $arProductBonus['bonus_all'];

			$arResult = array(
				'goods' => $arProdInfo,
				'qty' => intval($this->fields['QUANTITY']),
			);
		} else {
			$arResult = array(
				'goods' => \goods_list::getGoodEmpty(),
				'qty' => intval($this->fields['QUANTITY']),
			);

			$arResult['goods']['xml_id'] = $this->fields['ARTICLE'];
			$arResult['goods']['title'] = $this->fields['NAME'];
			$arResult['goods']['pack_only'] = false;
			$arResult['goods']['price']['actual'] = $this->fields['PRICE'];
			$arResult['goods']['price']['old'] = ($this->fields['DISCOUNT_PRICE'] > 0 ? $this->fields['DISCOUNT_PRICE'] + $this->fields['PRICE'] : '');
		}

		return $arResult;
	}
}