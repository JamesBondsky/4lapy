<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\Share;
use Generator;

/** @noinspection LongInheritanceChainInspection
 *
 * Class ShareCollection
 *
 * @package FourPaws\BitrixOrm\Collection
 */
class ShareCollection extends IblockElementCollection
{
    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @inheritdoc
     */
    protected function fetchElement(): Generator
    {
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new Share($fields);
        }
    }
}
