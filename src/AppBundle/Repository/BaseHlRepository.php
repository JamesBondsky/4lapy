<?php

namespace FourPaws\AppBundle\Repository;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use JMS\Serializer\Exception\RuntimeException;
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
     * @param ValidatorInterface $validator
     *
     * @throws RuntimeException
     * @throws \Exception
     */
    public function __construct(
        ValidatorInterface $validator
    )
    {
        parent::__construct($validator);
        $baseHl = HLBlockFactory::createTableObject(static::HL_NAME);
        $this->setDataManager($baseHl);
    }
}