<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Model\Interfaces\ImageInterface;

class ImageProcessor
{
    public function processImage(string $id, Collection $imageCollection)
    {
        $image = $this->findImage($id, $imageCollection);
        return $image ? $image->getSrc() : '';
    }

    /**
     * @param string     $id
     * @param Collection $collection
     *
     * @return null|ImageInterface
     */
    public function findImage(string $id, Collection $collection)
    {
        if (!$id) {
            return null;
        }
        return $collection
            ->filter(function ($item) {
                return $item instanceof ImageInterface;
            })
            ->filter(function (ImageInterface $image) use ($id) {
                return $image->getId() === (int)$id;
            })
            ->current();
    }
}
