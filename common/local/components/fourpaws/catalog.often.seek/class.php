<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\OftenSeek;
use FourPaws\CatalogBundle\Service\OftenSeekInterface;
use FourPaws\Helpers\TaggedCacheHelper;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use WebArch\BitrixCache\BitrixCache;

/** @noinspection AutoloadingIssuesInspection */
class CatalogOftenSeekComponent extends CBitrixComponent
{
    /** @var OftenSeekInterface $oftenSeekService */
    private $oftenSeekService;

    /**
     * CFourPawsFoodSelectionComponent constructor.
     *
     * @param \CBitrixComponent|null $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(\CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }

        $this->oftenSeekService = $container->get(OftenSeekInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function onPrepareComponentParams($params): array
    {
        if (!empty($params['SECTION_ID'])) {
            $params['SECTION_ID'] = (int)$params['SECTION_ID'];
        }

        $params['LEFT_MARGIN'] = (int)$params['LEFT_MARGIN'];
        $params['RIGHT_MARGIN'] = (int)$params['RIGHT_MARGIN'];
        $params['DEPTH_LEVEL'] = (int)$params['DEPTH_LEVEL'];

        /** кешируем на минуту - чтобы снизить нагрузку при оновременных запросах и в тоже время сохранить рандомную сортировку */
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?: 60;

        return $params;
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    public function executeComponent()
    {
        global $APPLICATION;

        if ($this->arParams['SECTION_ID'] <= 0) {
            return null;
        }

//        TaggedCacheHelper::addManagedCacheTags([
//            'catalog:often_seek:' . $this->arParams['SECTION_ID'],
//        ]);

        $getItems = function() {
            return $this->oftenSeekService->getItems(
                $this->arParams['SECTION_ID'],
                $this->arParams['LEFT_MARGIN'],
                $this->arParams['RIGHT_MARGIN'],
                $this->arParams['DEPTH_LEVEL']
            );
        };

        $bitrixCache = new BitrixCache();
        $bitrixCache->withId('section_' . $this->arParams['SECTION_ID']);
        $bitrixCache->withTag('catalog:often_seek:' . $this->arParams['SECTION_ID']);
        $bitrixCache->withTime(30*60*60*24);

        $this->arResult['ITEMS'] = $bitrixCache->resultOf($getItems)['result'];

        // чтобы фильтры складывались
        $curPageParam = $this->getParamsFromUrl($APPLICATION->GetCurPageParam());
        if(!empty($curPageParam)){
            /** @var OftenSeek $item */
            foreach ($this->arResult['ITEMS'] as $i => $item){
                $curPageParam = $this->getParamsFromUrl($APPLICATION->GetCurPageParam());
                $itemParam = $this->getParamsFromUrl($item->getLink());
                if(!$itemParam){
                    continue;
                }

                // провоцирует баг верстки
                unset($curPageParam['partitial'], $curPageParam['partial']);

                $isChosen = true;
                foreach ($itemParam as $key => $param){
                    if(!$curPageParam[$key]){
                        $isChosen = false;
                        break;
                    }

                    $arItemValue = explode(',', $param);
                    $arCurPageValue = explode(',', $curPageParam[$key]);

                    if(count(array_diff($arItemValue, $arCurPageValue)) > 0){
                        $isChosen = false;
                        break;
                    }
                }
                $item->setChosen($isChosen);

                foreach ($itemParam as $key => $value){
                    if(!empty($curPageParam[$key])){
                        $newValue = $curPageParam[$key] . ',' . $value;
                        $arNewValue = explode(',', $newValue);
                        $curPageParam[$key] = implode(',', array_unique($arNewValue));
                        unset($itemParam[$key]);
                    }
                }

                $newParams = array_merge($curPageParam, $itemParam ?: []);
                $newLink = sprintf('%s?%s', $APPLICATION->GetCurPage(false), http_build_query($newParams));
                $item->setLink($newLink);
                $this->arResult['ITEMS'][$i] = $item;
            }
        }

        $this->includeComponentTemplate();

        return true;
    }

    public function getParamsFromUrl($url){
        $result = [];
        preg_match('/\?(.*)/', $url, $matches);
        if(!empty($matches[1])) {
            $params = explode('&',$matches[1]);
            foreach ($params as $param){
                $arValue = explode("=", $param);
                $result[$arValue[0]] = str_replace('%2C', ',', $arValue[1]);
            }
        }

        return $result;
    }
}
