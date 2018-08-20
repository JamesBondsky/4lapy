<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\CdbResultCollectionBase;
use FourPaws\Catalog\Model\Category;
use Generator;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class CategoryCollection
 *
 * @package FourPaws\Catalog\Collection
 */
class CategoryCollection extends CdbResultCollectionBase
{
    /**
     * @return Generator
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    protected function fetchElement(): Generator
    {
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new Category($fields);
        }
    }
}
