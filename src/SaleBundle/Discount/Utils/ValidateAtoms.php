<?php
/**
 * Created by PhpStorm.
 * Date: 14.03.2018
 * Time: 16:15
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;

use FourPaws\SaleBundle\Exception\RuntimeException;


/**
 * Trait ValidateAtoms
 * @package FourPaws\SaleBundle\Discount\Utils
 */
trait ValidateAtoms
{
    private static $validationSkipList = ['list', 'element', 'section', 'iblock', 'user']; // дефолтные валидаторы
    /**
     *
     * @param $arValues - введенные пользователем значения параметров
     * @param $arParams - не используется и непонятно зачем нужен впинципе, данные есть и в других массивах.
     * @param $arControl - массив с описанием параметров обработчика правила
     * @param $boolShow - ВРОДЕ БЫ КАК переключалка режима - для сохранения либо отображения в админке
     *
     * @throws \FourPaws\SaleBundle\Exception\RuntimeException
     *
     * @return array|bool
     */
    public static function ValidateAtoms($arValues, $arParams, $arControl, $boolShow)
    {
        if (\is_callable('parent::ValidateAtoms')) {
            /** @noinspection PhpUndefinedClassInspection */
            $result = parent::ValidateAtoms(
                $arValues,
                $arParams,
                $arControl,
                $boolShow
            );
        } else {
            throw new RuntimeException('Ошибка привязки трейта: отсутствует родительский метод.');
        }

        if (
            \is_array($result) && !isset($result['err_cond']) && \is_array($arControl['ATOMS'])
        ) {
            foreach ($arControl['ATOMS'] as $atom) {
                $paramId = $atom['ATOM']['ID'];
                if (!isset($atom['ATOM']['VALIDATE']) || empty($atom['ATOM']['VALIDATE'])) {
                    $result['values'][$paramId] = $arValues[$paramId];
                    continue;
                }
                if (\in_array($atom['ATOM']['VALIDATE'], self::$validationSkipList, true)) {
                    continue;
                }
                if (
                    \in_array($atom['ATOM']['FIELD_TYPE'], ['double', 'int'], true)
                    && \is_array($atom['ATOM']['VALIDATE'])
                    && \count($atom['ATOM']['VALIDATE']) > 1
                ) {
                    [$operation, $operand] = $atom['ATOM']['VALIDATE'];
                    switch ($operation) {
                        case '>':
                            if ($arValues[$paramId] > $operand) {
                                $result['values'][$paramId] = $arValues[$paramId];
                            } else {
                                unset($result['values'][$paramId]);
                                $messages[] = 'Значение должно быть больше ' . $operand;
                            }
                            break;
                        case '<':
                            if ($arValues[$paramId] < $operand) {
                                $result['values'][$paramId] = $arValues[$paramId];
                            } else {
                                unset($result['values'][$paramId]);
                                $messages[] = 'Значение должно быть меньше ' . $operand;
                            }
                            break;
                        case '>=':
                            if ($arValues[$paramId] >= $operand) {
                                $result['values'][$paramId] = $arValues[$paramId];
                            } else {
                                unset($result['values'][$paramId]);
                                $messages[] = 'Значение должно быть больше или равно ' . $operand;
                            }
                            break;
                        case '<=':
                            if ($arValues[$paramId] <= $operand) {
                                $result['values'][$paramId] = $arValues[$paramId];
                            } else {
                                unset($result['values'][$paramId]);
                                $messages[] = 'Значение должно быть меньше или равно ' . $operand;
                            }
                            break;
                        default:
                            unset($result['values'][$paramId]);
                            $messages[] = 'Неизвестная операция для проверки значения.';
                    }
                }
            }
        }

        /**
         * К моему сожалению оказалось, что если передать сообщения об ошибке, то в админке они не отобразатся.
         * Более того, сбросятся все уже введеные значения. Поэтому решил, пока не разберусь почему так, не передавать их.
         */

        return $result;
    }
}