<?php

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\Iblock\Iblock;
use FourPaws\Catalog\Exception\ShareMoreOneFoundException;
use FourPaws\Catalog\Exception\ShareNotFoundException;
use FourPaws\Catalog\Model\Share;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\ShareQuery;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\SystemException;
use FourPaws\Enum\IblockType;
use FourPaws\LocationBundle\LocationService;
use FourPaws\App\Application as App;

class ShareService
{
    /**
     * Процент начисляемых бонусов для заводчиков (группа Избранное)
     */
    public const BONUS_OPT_PERCENT = 5;
    
    /**
     * @param string $shareCode
     *
     * @return Share
     * @throws ShareNotFoundException
     * @throws ShareMoreOneFoundException
     */
    public function getByCode(string $shareCode): Share
    {
        $shareCollection = (new ShareQuery())->withFilterParameter('=CODE', $shareCode)->exec();
        
        if ($shareCollection->isEmpty()) {
            throw new ShareNotFoundException(
                sprintf('Акция с кодом `%s` не найдена.', $shareCode)
            );
        }
        
        if ($shareCollection->count() > 1) {
            throw new ShareMoreOneFoundException(
                sprintf('Найдено более одного бренда с кодом `%s`', $shareCode)
            );
        }
        
        return $shareCollection->current();
    }
    
    /**
     * @param Share $share
     * @return Category
     * @throws IblockNotFoundException
     */
    public function getShareCategory(Share $share): Category
    {
        return Category::createRoot()
            ->withName(sprintf('Товары акции %s', $share->getName()));
    }
    
    /**
     * @return int
     */
    public static function getBonusOptPercent(): int
    {
        return self::BONUS_OPT_PERCENT;
    }
    
    /**
     * @param int $id
     * @param     $share
     * @return array
     */
    public function getParams($id, $share): array
    {
        $arParams                              = [];
        
        $arParams['IBLOCK_TYPE']               = IblockType::PUBLICATION;
        $arParams['IBLOCK_ID']                 = $share->getIblockId();
        // $arParams['DETAIL_FIELD_CODE']         = ;
        $arParams['VARIABLES']['ELEMENT_ID']   = $share->getId();
        $arParams['VARIABLES']['ELEMENT_CODE'] = $share->getCode();
        $arParams['USE_SHARE']                 = 'Y';
        $arParams['ADD_ELEMENT_CHAIN']         = 'N';
        $arParams['STRICT_SECTION_CHECK']      = 'N';
        $arParams['URL_REDIRECT_404']          = '/shares/';
        
        return $arParams;
    }
}
