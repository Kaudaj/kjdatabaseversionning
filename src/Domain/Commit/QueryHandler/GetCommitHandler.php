<?php
/**
 * Copyright since 2019 Kaudaj
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@kaudaj.com so we can send you a copy immediately.
 *
 * @author    Kaudaj <info@kaudaj.com>
 * @copyright Since 2019 Kaudaj
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace Kaudaj\Module\DBVCS\Domain\Commit\QueryHandler;

use Kaudaj\Module\DBVCS\Domain\Commit\Exception\CommitException;
use Kaudaj\Module\DBVCS\Domain\Commit\Exception\CommitNotFoundException;
use Kaudaj\Module\DBVCS\Domain\Commit\Query\GetCommit;
use Kaudaj\Module\DBVCS\Entity\Commit;
use PrestaShopException;

/**
 * Class GetCommitHandler is responsible for getting commit entity.
 *
 * @internal
 */
final class GetCommitHandler extends AbstractCommitQueryHandler
{
    /**
     * @throws PrestaShopException
     * @throws CommitNotFoundException
     */
    public function handle(GetCommit $query): Commit
    {
        try {
            $commit = $this->getCommitEntity(
                $query->getCommitId()->getValue()
            );
        } catch (PrestaShopException $e) {
            throw new CommitException(sprintf('An unexpected error occurred when retrieving commit with id %s', var_export($query->getCommitId()->getValue(), true)), 0, $e);
        }

        return $commit;
    }
}
