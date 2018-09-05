<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\Article;
use Generator;

/** @noinspection LongInheritanceChainInspection
 *
 * Class ArticleCollection
 *
 * @package FourPaws\BitrixOrm\Collection
 */
class ArticleCollection extends IblockElementCollection
{
    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @inheritdoc
     */
    protected function fetchElement(): Generator
    {
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new Article($fields);
        }
    }
}
