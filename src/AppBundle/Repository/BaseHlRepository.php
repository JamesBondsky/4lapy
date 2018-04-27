<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

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
     * @param ValidatorInterface        $validator
     * @param ArrayTransformerInterface $arrayTransformer
     *
     * @throws \Exception
     */
    public function __construct(
        ValidatorInterface $validator,
        ArrayTransformerInterface $arrayTransformer
    ) {
        parent::__construct($validator, $arrayTransformer);

        // даем возможность определить DataManager иным способом
        if (!$this->getDataManager()) {
            $baseHl = HLBlockFactory::createTableObject(static::HL_NAME);
            $this->setDataManager($baseHl);
        }
    }
}
