<?php
/**
 * Created by PhpStorm.
 * Date: 27.12.2017
 * Time: 21:13
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */
declare(strict_types=1);

namespace FourPaws\SaleBundle\Exception;

use Bitrix\Main\Result;

/**
 * Class BitrixProxyException - для формирования исключения из Bitrix\Result
 * @package FourPaws\SaleBundle\Exception
 */
class BitrixProxyException extends \Exception implements BaseExceptionInterface
{

    const UNDEFINED_EXCEPTION = 0;
    const NO_IBLOCK_ELEMENT = 100;
    const SALE_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY = 101;

    private static $messages = [
        self::NO_IBLOCK_ELEMENT => 'Товар не найден',
        self::SALE_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY => 'Товар не найден',
        //тут имеется в виду, что нет записи b_catalog_product
        self::UNDEFINED_EXCEPTION => 'Неизвестная ошибка',
    ];
    /** @noinspection PhpUndefinedClassInspection */

    /**
     * BitrixProxyException constructor.
     *
     * @param Result $result
     * @param \Throwable|null $previous
     */

    public function __construct(
        Result $result,
        /** @noinspection PhpUndefinedClassInspection */
        \Throwable $previous = null
    ) {
        $code = '';
        if ($error = $result->getErrors()) {
            $error = current($error);
            $code = $error->getCode();
        }
        $intCode = self::getIntCode($code);
        parent::__construct(
            self::makeMessage($intCode),
            $intCode,
            $previous
        );
    }

    /**
     *
     * @param string $bitrixCode
     *
     * @return int
     */
    public static function getIntCode(string $bitrixCode): int
    {
        return \constant('self::' . $bitrixCode) ?? self::UNDEFINED_EXCEPTION;
    }

    /**
     *
     * @param int $code
     *
     * @return string
     */
    public static function makeMessage(int $code): string
    {
        return self::$messages[$code] ?? self::$messages[self::UNDEFINED_EXCEPTION];
    }
}