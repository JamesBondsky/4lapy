<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Entity\ExpressionField;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use function Sodium\randombytes_random16;

class CatalogUniqueCodeSet20171220161027 extends SprintMigrationBase
{
    protected $description = 'Catalog Section Unique Code Set';

    public function up(): bool
    {

        /** @noinspection PhpUnhandledExceptionInspection */
        $codes = $this->getBaseQuery()
            ->addSelect('CODE')
            ->addSelect('COUNT')
            ->addFilter('>COUNT', 1)
            ->addOrder('COUNT', 'DESC')
            ->registerRuntimeField(
                'COUNT',
                new ExpressionField(
                    'COUNT',
                    'COUNT(%s)',
                    ['CODE'],
                    ['data_type' => 'integer']
                )
            )
            ->exec()
            ->fetchAll();

        $codes = array_map(function ($element) {
            return $element['CODE'];
        }, $codes);

        $codes = array_filter($codes);

        $this->log()->debug(sprintf('Найдено: %s неуникальных кодов', \count($codes)));
        if ($codes) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $sections = $this->getBaseQuery()
                ->addSelect('ID')
                ->addSelect('CODE')
                ->addSelect('NAME')
                ->addFilter('CODE', $codes)
                ->addOrder('DEPTH_LEVEL', 'DESC')
                ->exec()
                ->fetchAll();

            foreach ($sections as $section) {
                try {
                    $code = $this->getUniqueCode($section['NAME']);
                    $result = SectionTable::update($section['ID'], [
                        'CODE' => $code,
                    ]);
                } catch (\Exception $e) {
                    $this
                        ->log()
                        ->error(sprintf(
                            'Ошибка при обновлении %s секции: %s',
                            $section['ID'],
                            $e->getMessage()
                        ));
                    continue;
                }

                if ($result->isSuccess()) {
                    $this
                        ->log()
                        ->debug(sprintf(
                            'Секция %s обновлена. Сивольный код: %s',
                            $section['ID'],
                            $code
                        ));
                } else {
                    $this
                        ->log()
                        ->error(sprintf(
                            'Ошибка при обновлении %s секции: %s',
                            $section['ID'],
                            implode(' | ', $result->getErrorMessages())
                        ));
                }
            }
        }
        return true;
    }

    protected function getBaseQuery()
    {
        return SectionTable::query()
            ->addFilter('IBLOCK.CODE', IblockCode::PRODUCTS)
            ->addFilter('IBLOCK.IBLOCK_TYPE_ID', IblockType::CATALOG);
    }

    protected function getUniqueCode(string $name)
    {
        $i = 0;
        do {
            $code = $this->generate($i > 0 ? $name . $i : $name);
            if ($i > 100) {
                $this->log()->error(sprintf(
                    'Cant create unique index to %s. Using md5($name.randombytes_random16())',
                    $name
                ));
                $code = md5($name . randombytes_random16());
                break;
            }
            $i++;
        } while (!$this->isUnique($code));
        return $code;
    }

    protected function generate(string $name)
    {
        $config = [
            'max_len'               => 100,
            'change_case'           => 'L',
            'replace_space'         => '-',
            'replace_other'         => '-',
            'delete_repeat_replace' => true,
        ];

        return \CUtil::translit($name, 'ru', $config);
    }

    protected function isUnique(string $code): bool
    {
        return 1 >= $this->getBaseQuery()
                ->addFilter('CODE', $code)
                ->exec()
                ->getSelectedRowsCount();
    }

    public function down()
    {
    }
}
