<?php

namespace FourPaws\ManzanaApiBundle\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements ManzanaApiExceptionInterface
{
    public const ERRORS = [
        1 => 'Пустой массив купонов',
        2 => 'Пустой массив выпусков купонов',
    ];
}
