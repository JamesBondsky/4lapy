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

	protected function getMethodNameByType($type) {
        $methodName = false;
        switch ($type) {
            case 'goods':
                $methodName = 'goods_item';
                break;
            case 'goods_list':
                $methodName = 'goods_item_list';
                break;
            case 'catalog':
                $methodName = 'categories';
                break;
            case 'news':
                $methodName = 'news';
                break;
            case 'action':
                $methodName = 'action';
                break;
        }
        return $methodName;
    }

    protected function getQueryDataByType($type, $banner, $cityId) {
        $queryData = [];

        switch ($type) {
            case 'goods':
            case 'goods_list':
            case 'catalog':
                $queryData = [
                    'token' => $this->User['token'],
                    'id' => $banner['PROPERTY_VALUES']['LINK']
                ];
                break;
            case 'news':
            case 'action':
                $queryData = [
                    'token' => $this->User['token'],
                    'type' => $banner['CODE'],
                    'info_id' => $banner['PROPERTY_VALUES']['LINK'],
                    'city_id' => $cityId
                ];
                break;
        }

        return $queryData;
    }

    protected function getTargetUrl($type, $banner, $cityId) {
        $methodName = $this->getMethodNameByType($type);
        $queryData = $this->getQueryDataByType($type, $banner, $cityId);
        $targetUrl = $banner['PROPERTY_VALUES']['LINK'];

        if ($methodName) {
            $targetUrl = 'https://'.SITE_SERVER_NAME_API.'/mobile-api-v2/' . $methodName . '/?' . http_build_query($queryData);
        }
        return $targetUrl;
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
