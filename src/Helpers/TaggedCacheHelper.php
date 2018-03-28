<?php

namespace FourPaws\Helpers;

use Bitrix\Main\Application;
use Bitrix\Main\Data\TaggedCache;
use Bitrix\Main\SystemException;

/**
 * Class TaggedCacheHelper
 *
 * @package FourPaws\Helpers
 */
class TaggedCacheHelper
{
    /**
     * @param array            $tags
     * @param TaggedCache|null $tagCache
     */
    public static function addManagedCacheTags(array $tags, TaggedCache $tagCache = null): void {
        if (!\defined('BX_COMP_MANAGED_CACHE')) {
            return;
        }

        if($tagCache === null) {
            try {
                $tagCache = Application::getInstance()->getTaggedCache();
            } catch (SystemException $e) {
                return;
            }
        }

        foreach ($tags as $tag) {
            $tagCache->registerTag($tag);
        }
    }

    /**
     * @param array $tags
     */
    public static function clearManagedCache(array $tags): void {
        if (!\defined('BX_COMP_MANAGED_CACHE')) {
            return;
        }

        try {
            $tagCache = Application::getInstance()->getTaggedCache();
        } catch (SystemException $e) {
            return;
        }

        foreach ($tags as $tag) {
            $tagCache->clearByTag($tag);
        }
    }
}
