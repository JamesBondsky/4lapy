<?php

namespace FourPaws\ProductAutoSort\Helper;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Exception;
use FourPaws\ProductAutoSort\Table\ElementPropertyConditionTable;
use FourPaws\ProductAutoSort\UserType\ElementPropertyConditionUserType;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ValueHelper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('ProductAutoSortValueHelper'));
    }

    /**
     * Синхронизирует значение для условия свойства элемента
     *
     * @param int   $ufId
     * @param int   $sectionId
     * @param int   $propertyId
     * @param mixed $value
     */
    public function syncValue(int $ufId, int $sectionId, int $propertyId, $value)
    {
        $existingItem = null;
        $newItem = null;
        try {
            $existingItem = ElementPropertyConditionTable::query()
                ->setSelect(['ID'])
                ->setFilter(
                    [
                        '=UF_ID'       => $ufId,
                        '=SECTION_ID'  => $sectionId,
                        '=PROPERTY_ID' => $propertyId,
                    ]
                )
                ->exec()
                ->fetch();

            /**
             * Пустая строка расценивается как незаполненное свойство
             */
            if (trim($value) == '') {
                $value = null;
            }

            $newItem = [
                'UF_ID'          => $ufId,
                'SECTION_ID'     => $sectionId,
                'PROPERTY_ID'    => $propertyId,
                'PROPERTY_VALUE' => $value,
            ];

            if (false == $existingItem) {
                $result = ElementPropertyConditionTable::add($newItem);
            } else {
                $result = ElementPropertyConditionTable::update(
                    $existingItem['ID'],
                    $newItem
                );
            }

            if (!$result->isSuccess()) {
                throw new RuntimeException(
                    sprintf(
                        'Ошибка синхронизации условия для свойства: %s',
                        BitrixUtils::extractErrorMessage($result)
                    )
                );
            }
        } catch (Exception $exception) {
            $this->log()->error(
                sprintf(
                    "[%s] %s (%s)\n%s\n",
                    \get_class($exception),
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception->getTraceAsString()
                ),
                [
                    'existingItem' => $existingItem,
                    'newItem'      => $newItem,
                ]
            );
        }
    }

    /**
     * Синхронизирует множество значений для условия свойства элемента
     *
     * @param int   $ufId
     * @param int   $sectionId
     * @param array $valueList
     */
    public function syncValueMulti(int $ufId, int $sectionId, array $valueList)
    {
        $existingItem = null;
        try {
            /**
             * Удалить все записи для данного ufId + sectionId, чтобы не пытаться отловить удалённые значения.
             */
            $dbExistingItemList = ElementPropertyConditionTable::query()
                ->setSelect(['ID'])
                ->setFilter(
                    [
                        '=UF_ID'      => $ufId,
                        '=SECTION_ID' => $sectionId,
                    ]
                )
                ->exec();
            while ($existingItem = $dbExistingItemList->fetch()) {
                $result = ElementPropertyConditionTable::delete($existingItem['ID']);

                if (!$result->isSuccess()) {
                    $this->log()->error(
                        sprintf(
                            'Ошибка удаления условия для свойства: %s',
                            BitrixUtils::extractErrorMessage($result)
                        ),
                        [
                            'existingItem' => $existingItem,
                        ]
                    );
                }
            }

            /**
             * Создать отдельные значения
             */
            foreach ($valueList as $serializedValue) {
                $value = unserialize($serializedValue, ['allowed_classes' => false]);
                if (!\is_array($value)) {
                    continue;
                }

                /**
                 * Пустая строка расценивается как незаполненное свойство
                 */
                if (trim($value[ElementPropertyConditionUserType::VALUE_PROP_VALUE]) == '') {
                    $value[ElementPropertyConditionUserType::VALUE_PROP_VALUE] = null;
                }

                $newItem = [
                    'UF_ID'          => $ufId,
                    'SECTION_ID'     => $sectionId,
                    'PROPERTY_ID'    => (int)$value[ElementPropertyConditionUserType::VALUE_PROP_ID],
                    'PROPERTY_VALUE' => $value[ElementPropertyConditionUserType::VALUE_PROP_VALUE],
                ];

                $result = ElementPropertyConditionTable::add($newItem);
                if (!$result->isSuccess()) {
                    throw new RuntimeException(
                        sprintf(
                            'Ошибка синхронизации условия для свойства: %s',
                            BitrixUtils::extractErrorMessage($result)
                        )
                    );
                }
            }
        } catch (Exception $exception) {
            $this->log()->error(
                sprintf(
                    "[%s] %s (%s)\n%s\n",
                    \get_class($exception),
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception->getTraceAsString()
                ),
                [
                    'existingItem' => $existingItem,
                ]
            );
        }
    }

    public function deleteValue(int $sectionId)
    {
        try {
            $dbValues = ElementPropertyConditionTable::query()
                ->setSelect(['ID'])
                ->setFilter(
                    [
                        '=SECTION_ID' => $sectionId,
                    ]
                )
                ->exec();
            while ($value = $dbValues->fetch()) {
                $result = ElementPropertyConditionTable::delete($value['ID']);

                if (!$result->isSuccess()) {
                    $this->log()->error(
                        sprintf(
                            'Ошибка удаления условия для свойства: %s',
                            BitrixUtils::extractErrorMessage($result)
                        ),
                        [
                            'value' => $value,
                        ]
                    );
                }
            }
        } catch (Exception $exception) {
            $this->log()->error(
                sprintf(
                    "[%s] %s (%s)\n%s\n",
                    \get_class($exception),
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception->getTraceAsString()
                )
            );
        }
    }

    /**
     * @return LoggerInterface
     */
    public function log()
    {
        return $this->logger;
    }
}
