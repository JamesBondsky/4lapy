<?php

namespace FourPaws\AppBundle\Repository;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class BaseHlRepository
 *
 * @package FourPaws\AppBundle\Repository
 */
class BaseHlRepository extends BaseRepository
{
    const HL_NAME = '';
    
    /**
     * BaseHlRepository constructor.
     *
     * @param \JMS\Serializer\ArrayTransformerInterface                 $arrayTransformer
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     *
     * @throws \Exception
     */
    public function __construct(
        ArrayTransformerInterface $arrayTransformer,
        ValidatorInterface $validator
    )
    {
        parent::__construct($arrayTransformer, $validator);
        $hlAddress = HLBlockFactory::createTableObject(static::HL_NAME);
        $this->setDataManager($hlAddress);
    }
}