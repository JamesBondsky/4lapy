<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use RuntimeException;

/**
 * Class UpdateNewsSef20180328160328
 *
 * @package Sprint\Migration
 */
class UpdateNewsSef20180328160328 extends SprintMigrationBase
{
    private const IBLOCK_TYPE = 'publications';
    private const IBLOCK_CODE = 'news';

    protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */ $description = 'Обновелние SEF для инфоблока новостей';

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @throws ArgumentException
     * @throws SystemException
     * @throws Exception
     * @throws RuntimeException
     * @throws ObjectPropertyException
     */
    public function up()
    {
        $this->updateIblock($this->getFields('/services/news/'));
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @throws ArgumentException
     * @throws SystemException
     * @throws Exception
     * @throws RuntimeException
     * @throws ObjectPropertyException
     */
    public function down()
    {
        $this->updateIblock($this->getFields('/company/news/'));
    }

    /**
     * @param string $dir
     *
     * @return array
     */
    private function getFields(string $dir): array
    {
        return [
            'LIST_PAGE_URL' => '#SITE_DIR#' . $dir,
            'DETAIL_PAGE_URL' => '#SITE_DIR#' . $dir . '#ELEMENT_CODE#/',
            'SECTION_PAGE_URL' => '#SITE_DIR#' . $dir,
            'CANONICAL_PAGE_URL' => 'https://#SERVER_NAME##SITE_DIR#/' . $dir . '#ELEMENT_CODE#/',
        ];
    }

    /**
     * @param array $fields
     *
     * @throws ArgumentException
     * @throws SystemException
     * @throws Exception
     * @throws RuntimeException
     * @throws ObjectPropertyException
     */
    private function updateIblock(array $fields)
    {
        /**
         * @var array $fetched
         */
        $fetched = (new Query(IblockTable::class))
            ->where('IBLOCK_TYPE_ID', self::IBLOCK_TYPE)
            ->where('CODE', self::IBLOCK_CODE)
            ->setSelect(['ID'])
            ->setLimit(1)
            ->exec()->fetch();

        if ($fetched['ID']) {
            $result = IblockTable::update($fetched['ID'], $fields);
            if ($result->isSuccess()) {
                $this->log()->info('Поля обновлены');

                return;
            }

            $this->log()->error(
                \sprintf(
                    'Ошибка обновления полей %s',
                    \implode(', ', $result->getErrorMessages())
                )
            );
        }

        throw new RuntimeException('Ошибка обновления');
    }
}
