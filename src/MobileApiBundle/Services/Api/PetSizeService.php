<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;


use FourPaws\MobileApiBundle\Tables\ClosingSizeTable;

class PetSizeService
{
    /**
     * @var $back int
     */
    protected $back;
    
    /**
     * @var $neck int
     */
    protected $neck;
    
    /**
     * @var $chest int
     */
    protected $chest;
    
    /**
     * @var $sizeIntervals array
     */
    protected $sizeIntervals;
    
    public function __construct($back, $neck, $chest)
    {
        $this->back = $back;
        $this->neck = $neck;
        $this->chest = $chest;
    }
    
    public function calculate()
    {
        $this->sizeIntervals = $this->getSizeIntervals();
        
        return $this->getPetSize();
    }
    
    protected function getSizeIntervals()
    {
        return ClosingSizeTable::query()
            ->setSelect(['*'])
            ->setCacheTtl('36000')
            ->exec()
            ->fetchAll();
    }
    
    protected function getPetSize()
    {
        foreach ($this->sizeIntervals  as $interval) {
            $backLength = false;
            $chestSize = false;
            $neckSize = false;
            
            if ($this->back >= $interval['UF_BACK_MIN'] && $this->back <= $interval['UF_BACK_MAX']) {
                $backLength = true;
            }
    
            if ($this->chest >= $interval['UF_CHEST_MIN'] && $this->chest <= $interval['UF_CHEST_MAX']) {
                $chestSize = true;
            }
    
            if ($this->neck >= $interval['UF_NECK_MIN'] && $this->neck <= $interval['UF_NECK_MAX']) {
                $neckSize = true;
            }
            
            if ($backLength && $chestSize && $neckSize) {
                return $interval['UF_CODE'];
            }
        }
        
        return false;
    }
}
