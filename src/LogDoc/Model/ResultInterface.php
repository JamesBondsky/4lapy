<?php

namespace FourPaws\LogDoc\Model;

interface ResultInterface
{
    /**
     * @return bool
     */
    function isSuccess(): bool;

    /**
     * @return \mixed
     */
    function getResult();
}
