<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 21.06.2019
 * Time: 14:11
 */

namespace FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe;


use Bitrix\Main\Type\DateTime as BitrixDateTime;

trait PropertiesFillingTrait
{
    /**
     * ConstructorTrait constructor.
     * @param $transferObjectType
     * @throws \Exception
     */
    public function fillProperties($transferObjectType)
    {
        $fields = get_object_vars($this);
        foreach($fields as $fieldCode => $fieldValue){
            $setter = 'set'.ucfirst($fieldCode);
            $getter = 'get'.ucfirst($fieldCode);
            if(method_exists($this, $setter) && method_exists($transferObjectType, $getter)){
                $value = $transferObjectType->$getter();
                if($value === null){
                    continue;
                }
                if($value instanceof BitrixDateTime){
                    $value = new \DateTime($value->toString());
                }
                $this->$setter($value);
            }
        }
    }
}