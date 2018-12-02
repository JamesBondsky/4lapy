<?php

use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\Catalog\Model\Offer;

class goods_list extends APIServer
{
    protected $type='token';
    static $arCardInfo = null;

    public static function getGoodEmpty()
    {
        return array(
            'id' => '',
            'xml_id' => '',
            'picture' => '',
            'picture_preview' => '',
            'title' => '',
            'info' => '',
            'in_pack' => '',
            'pack_only' => '',
            'discount_text' => '',
            'webpage' => '',
            'tag' => array(),
            'category' => array(
                'id' => '',
                'title' => ''
            ),
            'price' => array(
                'actual' => '',
                'old' => ''
            ),
            'bonus_user' => '',
            'bonus_all' => '',
        );
    }

    //получаем инфу о товаре в нужном формате
    public function GetProdInfo($offerIds)
    {
        $result = false;
        
        if (empty($offerIds)) {
            return $result;
        }
        
        CModule::IncludeModule('iblock');
        
        $res = (new OfferQuery())
            ->withFilterParameter('ID', $offerIds)
            ->exec();

        //КОСТЫЛЬ: убираем из выдачи товары от поставщиков
        /*
        $arProviders = GeoCatalog::GetProvidersStores();
        if ($arProviders)
        {
            foreach ($arProviders as $iStoreId)
            {
                $arFilter[] = array(
                    'LOGIC'=> 'OR',
                    array("=CATALOG_STORE_AMOUNT_".$iStoreId => false),
                    array("=CATALOG_STORE_AMOUNT_".$iStoreId => 0)
                );
            }
        }
        */
        // echo "<pre>";print_r($arProviders);echo "</pre>"."\r\n";
        // echo "<pre>";print_r($arFilter);echo "</pre>"."\r\n";
        //!КОСТЫЛЬ: убираем из выдачи товары от поставщиков

        /** @var Offer $offer */
        foreach ($res->getValues() as $offer) {
            $oProduct = $offer->getProduct();
            $productCategory = $oProduct->getCategory()->toArray();
            $offer = $offer->toArray();
            $tags = [];

            if ($offer['PROPERTY_IS_HIT_VALUE'] != false)
                $tags[] = [
                    'img' => MarkHelper::MARK_HIT_IMAGE_SRC,
                ];

            if ($offer['PROPERTY_IS_NEW_VALUE'] != false)
                $tags[] = [
                    'img' => MarkHelper::MARK_NEW_IMAGE_SRC,
                ];

            if ($offer['PROPERTY_IS_SALE_VALUE'] != false)
                $tags[] = [
                    'img' => MarkHelper::MARK_SALE_IMAGE_SRC,
                ];
            $result[$offer['ID']] = [
                'id' => $offer['ID'],
                'xml_id' => $offer['XML_ID'],
                'picture' => ($offer['PROPERTY_VALUES']['IMG'][0]) ? 'https://'.SITE_SERVER_NAME_API.CFile::GetPath($offer['PROPERTY_VALUES']['IMG'][0]) : '',
                // 'picture_preview' => ($arOffer['PROPERTY_VALUES']['IMG'][0]) ? 'https://'.SITE_SERVER_NAME_API.$file['src'] : '',
                'title' => $offer['NAME'],
                'info' => '',
                // 'info' => ($offer['PROPERTY_DESCRIPTION_CARD_VALUE']['TEXT']) ? $offer['PROPERTY_DESCRIPTION_CARD_VALUE']['TEXT'] : '',
                'in_pack' => ($offer['PROPERTY_VALUES']['MULTIPLICITY']) ? $offer['MULTIPLICITY'] : '',
                // 'pack_only' => ($offer['PROPERTY_VALUES']['MULT_ONLY'] === 'Y') ? true : false,
                'discount_text' => '', //нет на сайте
                'webpage' => 'https://'.SITE_SERVER_NAME_API.$offer['DETAIL_PAGE_URL'],
                'tag' => ($tags) ?: [],
                'brand_name' => $oProduct->getBrandName(),
                'category' => [
                    'id' => $productCategory['UF_XML_ID'],
                    'title' => $productCategory['UF_NAME'],
                ],
                'price' => [
                    'actual' => $offer['price'],
                    'old' => $offer['oldPrice'] > 0 ? $offer['oldPrice'] : '',
                ]
            ];
        }

        return $result;
    }

    public function GetProductBonus($arPrice, $arProdInfo)
    {
        if (!self::$arCardInfo)
        {
            if ($this->User['UF_DISCOUNT_CARD'] and $this->User['UF_DISCOUNT_CARD'] != '')
            {
                self::$arCardInfo = \FourPaws\MobileApiOldBundle\Card::UpdateDataCard_ml($this->User['UF_DISCOUNT_CARD'], $bReturnData = true);
                // echo "<pre>+"; print_r(self::$arCardInfo); echo "</pre>";
            }
        }

        $arProductBonus = array(
            'bonus_user' => 0,
            'bonus_all' => 0
        );

        //если на товар нет скидки
        if ((empty($arPrice['old'])) or (($arPrice['actual']/$arPrice['old']) >= 0.96))
        {
            //если пользователь авторизован, то высчитываем бонусы в зависимости от его карты,
            //в противном случаем выводим 3 процента
            $arProductBonus['bonus_all'] = $arPrice['actual'] * 0.03;

            if ($this->User['user_id'] > 0)
            {
                $iDiscountPercent = (!empty(self::$arCardInfo['DISCOUNT'])) ? self::$arCardInfo['DISCOUNT'] : 3;
                $arProductBonus['bonus_user'] = $arPrice['actual'] * $iDiscountPercent / 100;
            }
            else
            {
                $arProductBonus['bonus_user'] =$arProductBonus['bonus_all'];
            }

            //если товар участвует в акции - удваиваем количество бонусов
            /*
            $iProdId = $arProdInfo['id'];
            if (!empty($arProdDetailInfo[$iProdId]['PROPERTY_ACTIONS_SHILDS_VALUE']))
            {
                //на боевом id этой акции 56
                if (array_key_exists(BONUS_ACTION_ID_X2, $arProdDetailInfo[$iProdId]['PROPERTY_ACTIONS_SHILDS_VALUE']))
                {
                    $arProductBonus['bonus_user'] = $arProductBonus['bonus_user'] * 2;
                    $arProductBonus['bonus_all'] = $arProductBonus['bonus_all'] * 2;
                }
                elseif (!array_key_exists('103', $arProdDetailInfo[$iProdId]['PROPERTY_ACTIONS_SHILDS_VALUE']))
                {
                    $arProductBonus['bonus_user'] = 0;
                    $arProductBonus['bonus_all'] = 0;
                }
            }
            */

            CModule::IncludeModule("iblock");
            $cacheTime = '86400';
            $arBrands = array(
                'whiskas', 
                'kitekat', 
                'felix', 
                'friskies', 
                'perfect fit', 
                'sheba', 
                'gourmet', 
                'cesar', 
                'pedigree', 
                'chappi'
            );

            $cacheSectionsDogFood = new CPHPCache;

            if ($cacheSectionsDogFood->InitCache($cacheTime, "|GetSections|DogFood", "/")) {
                $arSectionsDogFood = $cacheSectionsDogFood->GetVars();
            } else {
                $arSectionsDogFood = array();
                $rsParentSection = CIBlockSection::GetByID(10157);
                if ($arParentSection = $rsParentSection->Fetch()){
                    $rsSect = CIBlockSection::GetList(
                        array('left_margin' => 'asc'),
                        array(
                            'IBLOCK_ID' => $arParentSection['IBLOCK_ID'],
                            '>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],
                            '<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],
                            '>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']
                        )
                    );
                    while ($arSect = $rsSect->Fetch()){
                        $arSectionsDogFood[] = $arSect['ID'];
                    }
                }
                $cacheSectionsDogFood->StartDataCache();
                $cacheSectionsDogFood->EndDataCache($arSectionsDogFood);
            }

            $cacheSectionsCatFood = new CPHPCache;

            if ($cacheSectionsCatFood->InitCache($cacheTime, "|GetSections|CatFood", "/")) {
                $arSectionsCatFood = $cacheSectionsCatFood->GetVars();
            } else {
                $arSectionsCatFood = array();
                $rsParentSection = CIBlockSection::GetByID(10297);
                if ($arParentSection = $rsParentSection->Fetch()){
                    $rsSect = CIBlockSection::GetList(
                        array('left_margin' => 'asc'),
                        array(
                            'IBLOCK_ID' => $arParentSection['IBLOCK_ID'],
                            '>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],
                            '<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],
                            '>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']
                        )
                    );
                    while ($arSect = $rsSect->Fetch()){
                        $arSectionsCatFood[] = $arSect['ID'];
                    }
                }
                $cacheSectionsCatFood->StartDataCache();
                $cacheSectionsCatFood->EndDataCache($arSectionsCatFood);
            }

            $category = $arProdInfo['category']['id'];
            $brand = $arProdInfo['brand_name'];

            if (is_array($category)) {
                foreach ($category as $category_value) {
                    if ( in_array($category_value, $arSectionsDogFood) or in_array($category_value, $arSectionsCatFood) ) {
                        if ( in_array(strtolower($brand), $arBrands) ) {
                            $arProductBonus['bonus_user'] = 0;
                            $arProductBonus['bonus_all'] = 0;
                            break;
                        }
                    }
                }
            } else {
                if ( in_array($category, $arSectionsDogFood) or in_array($category, $arSectionsCatFood) ) {
                    if ( in_array(strtolower($brand), $arBrands) ) {
                        $arProductBonus['bonus_user'] = 0;
                        $arProductBonus['bonus_all'] = 0;
                    }
                }
            }

            //округляем хз как
            //23.08.2017 модифицируем количество бонусов
            $bonusModifier = 1;
            $arProductBonus['bonus_user'] = round($arProductBonus['bonus_user']) * $bonusModifier;
            $arProductBonus['bonus_all'] = round($arProductBonus['bonus_all']) * $bonusModifier;
        }

        return $arProductBonus;
    }

    //проверка существавания товара
    public function CheckProductExistence($iProdId)
    {
        CModule::IncludeModule('iblock');

        //получаем список активных конкурсов (разделов) а также информацию по ним
        $arProduct = \CIBlockElement::GetList(
            array(),
            array(
                'IBLOCK_ID' => ROOT_CATALOG_ID,
                'ID' => $iProdId,
                'ACTIVE' => 'Y',
            ),
            false,
            array(
                'ID',
                'NAME'
            )
        )->Fetch();

        return $arProduct ? true : false;
    }

    public function get($arInput)
    {
        CModule::IncludeModule('iblock');
        CModule::IncludeModule('catalog');

        $iSectionID = -1;
        $iPageNum = 1;
        $iPageCount = API_SHOW_EL_COUNT;

        $delivery = false;
        $delivery_sam = false;

        // проверяем существование ключей и формат
        if (array_key_exists('category_id', $arInput))
        {
            if (is_numeric($arInput['category_id']) && $arInput['category_id'] > 0)
                $iSectionID = $arInput['category_id'];
        }
        if (array_key_exists('page', $arInput))
        {
            if (is_numeric($arInput['page']) && $arInput['page'] > 0)
                $iPageNum = $arInput['page'];
        }
        if (array_key_exists('count', $arInput))
        {
            if (is_numeric($arInput['count']) && $arInput['count'] > 0)
                $iPageCount = $arInput['count'];
        }

        $arResult = array(
            'total_items' => 0,
            'total_pages' => 0,
            'goods' => array()
        );


        if ($iSectionID > -1)
        {
            $arFilter = array(
                'IBLOCK_ID' => ROOT_CATALOG_ID,
                'SECTION_ID' => $iSectionID,
                'ACTIVE' => 'Y',
                'INCLUDE_SUBSECTIONS' => 'Y',
                // '><CATALOG_PRICE_1' => array(1000, 2000)
                // 'PROPERTY_BRAND_NAME' => array('ЭВЕРКЛИН', 'ЭДЕЛЬ КЭТ', 'ЭВАНДЖЕРС')
            );

            //фильтр по наличию товара в городе
            if (!empty($arInput['city_id']))
            {
                $arStores = GeoCatalog::GetShopsIdByCity($arInput['city_id']);

                // echo "<pre>"; print_r($arStores); echo "</pre>";
                if ($arStores)
                {
                    $arFilter['0'] = array(
                        "LOGIC" => "OR",
                        "CS" => array(">PROPERTY_STOCK" => 0),
                    );
                    foreach ($arStores as $iStoreId)
                    {
                        $arFilter['0'][] = array(">=CATALOG_STORE_AMOUNT_".$iStoreId => 1);
                    }
                }
            }

            //формируем фильтры из запроса
            if (!empty($arInput['filters']) and is_array($arInput['filters']))
            {
                // echo "<pre>"; print_r($arInput['filters']); echo "</pre>";
                foreach ($arInput['filters'] as $arInputFilter)
                {
                    if ($arInputFilter['id'] != 'base')
                    {
                        $arFilter['PROPERTY_'.$arInputFilter['id']] = $arInputFilter['value'];
                        if($arInputFilter['id'] == 'purchase')
                        {
                            $delivery = ($arInputFilter['value']['0'])?true:false;
                            $delivery_sam = ($arInputFilter['value']['1'])?true:false;
                        }
                    }
                    else
                    {
                        //сортируем на всякий случай
                        asort($arInputFilter['value']);
                        $arFilter['><CATALOG_PRICE_1'] = $arInputFilter['value'];
                    }
                }
            }

            //модифицируем фильтр по наличию товара, если пришли фильтры "Доступно для доставки" или "Доступно для самовывоза"
            if(($delivery or $delivery_sam) and !empty($arInput['city_id'])):
                if($delivery and $delivery_sam):
                    $arFilter['0']['LOGIC'] = 'AND';
                elseif($delivery):
                    unset($arFilter['0']);
                    $arFilter[">PROPERTY_STOCK"] = '0';
                elseif($delivery_sam):
                    unset($arFilter['0']['CS']);
                endif;
            endif;

            //формируем сортировки
            if (!empty($arInput['sorts']) and is_array($arInput['sorts']))
            {
                foreach ($arInput['sorts'] as $arInputSort)
                {
                    $arSort[$arInputSort['id']] = $arInputSort['value'];
                }
            }
            else
                $arSort = array('SORT' => 'ASC');

            $arSort['ID'] = 'ASC';
            // echo "<pre>"; print_r($arSort); echo "</pre>";

            //КОСТЫЛЬ: убираем из выдачи товары от поставщиков
            $arProviders = GeoCatalog::GetProvidersStores();
            if ($arProviders)
            {
                foreach ($arProviders as $iStoreId)
                {
                    $arFilter[] = array(
                        'LOGIC'=> 'OR',
                        array("=CATALOG_STORE_AMOUNT_".$iStoreId => false),
                        array("=CATALOG_STORE_AMOUNT_".$iStoreId => 0)
                    );
                }
            }
            // echo "<pre>";print_r($arProviders);echo "</pre>"."\r\n";
            // echo "<pre>";print_r($arFilter);echo "</pre>"."\r\n";
            //!КОСТЫЛЬ: убираем из выдачи товары от поставщиков

            //получаем список ID товаров по заданным параметрам
            $oProductsList = CIBlockElement::GetList(
                $arSort,
                $arFilter,
                false,
                array(
                    'iNumPage' => $iPageNum,
                    'nPageSize' => $iPageCount
                ),
                array(
                    'ID',
                    'NAME'
                )
            );
            while ($arProduct = $oProductsList->GetNext())
            {
                $arProdId[] = $arProduct['ID'];
            }

            //получаем цены товаров из списка
            $oPrice = CPrice::GetList(
                array(),
                array(
                    "PRODUCT_ID" => $arProdId,
                ),
                false,
                false,
                array(
                    'ID',
                    'PRODUCT_ID',
                    'CATALOG_GROUP_ID',
                    'PRICE'
                )
            );
            while ($arPrice = $oPrice->Fetch())
            {
                $arProdPrices[$arPrice['PRODUCT_ID']][$arPrice['CATALOG_GROUP_ID']] = $arPrice['PRICE'];
            }

            //тащим инфу по выбранным товарам
            if ($arProdInfoList = $this->GetProdInfo($arProdId))
            {
                foreach ($arProdId as $iProdId)
                {
                    $arProdInfo = $arProdInfoList[$iProdId];

                    //формируем поле 'price' в зависимости от наличия скидки
                    if ($arProdPrices[$iProdId][2] > 0)
                    {
                        $arProdInfo['price'] = array(
                            'actual' => $arProdPrices[$iProdId][2],
                            'old' => $arProdPrices[$iProdId][1]
                        );
                    }
                    else
                    {
                        $arProdInfo['price'] = array(
                            'actual' => $arProdPrices[$iProdId][1],
                            'old' => ''
                        );
                    }

                    //получаем количество бонусов по позиции
                    $arProductBonus = $this->GetProductBonus($arProdInfo['price'],$arProdInfo);

                    //округляем хз как
                    $arProdInfo['bonus_user'] = ceil($arProductBonus['bonus_user']);
                    $arProdInfo['bonus_all'] = ceil($arProductBonus['bonus_all']);

                    //формируем результирующий массив
                    $arResult['goods'][] = $arProdInfo;
                }
                //формируем результирующий массив
                $arResult['total_items'] = $oProductsList->NavRecordCount;
                $arResult['total_pages'] = $oProductsList->NavPageCount;
            }
            // else
                // $this->res['errors']+=$this->ERROR['error_get_prod_info'];
        }
        else
            $this->res['errors']+=$this->ERROR['required_params_missed'];

        // echo "<pre>"; print_r($arResult); echo "</pre>";

        return($arResult);
    }
}
?>