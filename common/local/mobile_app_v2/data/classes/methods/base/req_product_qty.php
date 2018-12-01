<?
/**
* объект запроса АПИ ОбъектКоличествоТовара
*/
class req_product_qty extends \req_param
{
	public function isRequired()
	{
		return true;
	}

	public function convApiToBx($arParams)
	{
		if ($this->verify($arParams)) {
			return array(
				'PRODUCT_ID' => intval($arParams['goods_id']),
				'QUANTITY' => intval($arParams['qty']),
			);
		} else {
			return array();
		}
	}

	public function verify($arParams)
	{
		return (
			isset($arParams['goods_id'])
			&& intval($arParams['goods_id'])
			&& isset($arParams['qty'])
		);
	}
}