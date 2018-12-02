<?

class promo_baner extends \APIServer
{

	public function get($arInput)
	{
        $cityId = ($arInput['city_id'] and strlen($arInput['city_id']) > 0)?$arInput['city_id']:null;
        $bannersList = new \baner_list;
        $result['banners'] = $bannersList->getBanners('mobile_promo', $cityId);
        return $result;
	}
}
