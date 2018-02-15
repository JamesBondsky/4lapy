<?php

namespace FourPaws\SapBundle\Source;


class CsvDirectorySource extends DirectorySource
{
    protected function convert($data)
    {
        return $data;
    }
}
