<?

use \FourPaws\Catalog\Query\BannerQuery;
use \FourPaws\Catalog\Model\Banner;

class baner_list extends \APIServer
{

	public function get($arInput)
	{
        $result = [];
        $bannerType = false;
        if (isset($arInput['tag']) && strlen($arInput['tag']) > 0) {
            $bannerType = $arInput['tag'];
        } else {
            $this->addError('required_params_missed');
        }

        if (!$this->hasErrors()) {
            $cityId = ($arInput['city_id'] and strlen($arInput['city_id']) > 0)?$arInput['city_id']:null;
            $result['banners'] = $this->getBanners($bannerType, $cityId);
        }

	    return $result;
	}

    public function getBanners($bannerType, $cityId = null) {
	    $result = [];
        $res = (new BannerQuery())
            ->withFilterParameter('ACTIVE', 'Y')
            ->withType($bannerType)
            ->exec();

        /** @var Banner $banner */
        foreach ($res->getValues() as $banner) {
            $type = $banner->getBannerType();
            $banner = $banner->toArray();
            $targetUrl = $this->getTargetUrl($type, $banner, $cityId);
            $result[] = [
                'id' => $banner['ID'],
                'picture' => ($banner['DETAIL_PICTURE']) ? 'https://'.SITE_SERVER_NAME_API.CFile::GetPath($banner['DETAIL_PICTURE']) : '',
                'delay' => 3, // API_SHOW_BANNER_TIME,
                'title' => ($banner['NAME'])?:'',
                'type' => $type,
                'target' => ($targetUrl)?:'',
                'target_alt' => ($banner['PROPERTY_VALUES']['LINK'])?:'',
            ];
        }
        return $result;
    }
}
