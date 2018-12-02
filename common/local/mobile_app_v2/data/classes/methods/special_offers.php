<?
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Model\Offer;

class special_offers extends \APIServer
{
	public function get($arInput)
	{
		$pageNum = intval($arInput['page']);
		$pageNum = ($pageNum ?: 1);
		$pageCount = intval($arInput['count']);
		$pageCount = ($pageCount ?: 10);

		$arResult = array(
			'total_items' => 0,
			'total_pages' => 0,
			'goods' => array()
		);

        // получаем список ID товаров по заданным параметрам
        $productsIds = [];

        $res = (new OfferQuery())
            ->withFilterParameter('ACTIVE', 'Y')
            ->withFilterParameter('!PROPERTY_IS_SALE_VALUE', false)
            ->withNav(['iNumPage' => $pageNum, 'nPageSize' => $pageCount])
            ->withOrder(['SORT' => 'ASC', 'NAME' => 'ASC'])
            ->withSelect(['ID'])
            ->exec();

        /** @var Offer $offer */
        foreach ($res->getValues() as $offer) {
            $productsIds[] = $offer->getId();
        }
        $cdbResult = $res->getCdbResult();

        if (count($productsIds) > 0) {
            $oGoodsList = new \goods_list;

            // тащим инфу по выбранным товарам
            if ($arProdInfoList = $oGoodsList->GetProdInfo($productsIds)) {

                foreach ($arProdInfoList as $iProdId => $arProdInfo) {

                    //получаем количество бонусов по позиции
                    $arProductBonus = $oGoodsList->GetProductBonus($arProdInfo['price'], $arProdInfo);

                    // округляем хз как
                    $arProdInfo['bonus_user'] = ceil($arProductBonus['bonus_user']);
                    $arProdInfo['bonus_all'] = ceil($arProductBonus['bonus_all']);

                    // формируем результирующий массив
                    $arResult['goods'][] = $arProdInfo;

                    $arResult['total_items'] = $cdbResult->NavRecordCount;
                    $arResult['total_pages'] = $cdbResult->NavPageCount;
                }
            } else {
                $this->addError('error_get_prod_info');
            }
        }

		return $arResult;
	}
}
