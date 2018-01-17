<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\FoodSelectionBundle\Service;

use FourPaws\FoodSelectionBundle\Repository\FoodSelectionRepository;

/**
 * Class FoodSelectionService
 *
 * @package FourPaws\FoodSelectionBundle\Service
 */
class FoodSelectionService
{
    /**
     * @var FoodSelectionRepository
     */
    private $foodSelectionRepository;
    
    /**
     * FoodSelectionService constructor.
     *
     * @param FoodSelectionRepository $foodSelectionRepository
     *
     */
    public function __construct(FoodSelectionRepository $foodSelectionRepository)
    {
        $this->foodSelectionRepository = $foodSelectionRepository;
    }
    
    /**
     * @param array $data
     *
     * @return array
     */
    public function findBy(array $data = []) : array
    {
        $params = [];
        if (!empty($data)){
            if(!empty($data[''])){
            
            }
        }
        return $this->foodSelectionRepository->findBy($params);
    }
}
