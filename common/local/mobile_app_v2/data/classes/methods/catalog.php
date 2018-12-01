<?php
	class catalog extends APIServer
	{
		protected $type='token';

		public function get($aInput){
			CModule::IncludeModule('catalog');
			$iCatalogID=0;
			// проверяем существование ключей и формат
			if(array_key_exists('catalog_id', $aInput)){
				if(is_numeric($aInput['catalog_id']) && $aInput['catalog_id']>0){
					$iCatalogID=$aInput['catalog_id'];
				}else{
					$iCatalogID=-1;
				}
			}
			$aResult=array('catalog'=>array(), 'good'=>array());
			if($iCatalogID>-1){
				// получаем список подкаталогов
				$aSectionList=CIBlockSection::GetList(
					array(
						'SORT'=>'ASC',
						'NAME'=>'ASC'
					),
					array(
						'IBLOCK_ID'=>ROOT_CATALOG_ID,
						'SECTION_ID'=>$iCatalogID,
						'ACTIVE'=>'Y',
						'GLOBAL_ACTIVE'=>'Y'
					)
				);
				while($aSection=$aSectionList->Fetch()){
					$aResult['catalog'][]=array('id'=>$aSection['ID'], 'caption'=>$aSection['NAME']);
				}
				// получаем список товаров
				$aGoodList=CIBlockElement::GetList(
					array(
						'SORT'=>'ASC',
						'NAME'=>'ASC'
					),
					array(
						'IBLOCK_ID'=>ROOT_CATALOG_ID,
						'SECTION_ID'=>$iCatalogID,
						'ACTIVE'=>'Y',
						//'INCLUDE_SUBSECTIONS'=>'Y'
					)
				);
				while($aGood=$aGoodList->Fetch()){
					$aResult['good'][]=array('id'=>$aGood['ID'], 'caption'=>$aGood['NAME']);
				}
			}
			return($aResult);
		}
	}
?>