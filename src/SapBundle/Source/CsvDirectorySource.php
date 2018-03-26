<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Source;

class CsvDirectorySource extends DirectorySource
{
    protected function convert($data)
    {
        return $data;
    }
}
