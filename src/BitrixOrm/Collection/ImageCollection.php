<?php

namespace FourPaws\BitrixOrm\Collection;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use Bitrix\Main\FileTable;
use FourPaws\BitrixOrm\Model\Image;
use InvalidArgumentException;

class ImageCollection extends ObjectArrayCollection
{
    public static function createFromIds(array $ids = [])
    {
        $collection = new static();
        if ($ids) {
            $result = FileTable::query()
                ->addFilter('ID', $ids)
                ->addSelect('*')
                ->exec();
            while ($item = $result->fetch()) {
                $collection->add(new Image($item));
            }
        }
        return $collection;
    }

    /**
     * @param mixed $object
     *
     * @throws InvalidArgumentException
     * @return void
     */
    protected function checkType($object)
    {
        if (!($object instanceof Image)) {
            throw new InvalidArgumentException('Переданный объект не является картинкой');
        }
    }
}
