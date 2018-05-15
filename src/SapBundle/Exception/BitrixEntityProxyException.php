<?php
/**
 * Created by PhpStorm.
 * Date: 29.03.2018
 * Time: 20:58
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SapBundle\Exception;


use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Entity\Result;
use Bitrix\Main\Entity\UpdateResult;

/**
 * Class BitrixProxyException
 * @package FourPaws\SapBundle\Exception
 */
class BitrixEntityProxyException extends \RuntimeException implements SapBundleException
{
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
        $mess = '';

        if ($result instanceof DeleteResult) {
            $mess = 'Ошибка удаления: ';
        } elseif ($result instanceof UpdateResult) {
            $mess = 'Ошибка обновления: ';
        } elseif ($result instanceof AddResult) {
            $mess = 'Ошибка добавления: ';
        }

        foreach ($result->getErrorMessages() as $message) {
            $mess .= $message;
        }

        parent::__construct(
            $mess,
            0,
            $previous
        );
    }
}