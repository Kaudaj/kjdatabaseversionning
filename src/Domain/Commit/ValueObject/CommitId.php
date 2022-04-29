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

namespace Kaudaj\Module\DBVCS\Domain\Commit\ValueObject;

use Kaudaj\Module\DBVCS\Domain\Commit\Exception\CommitException;

/**
 * Class CommitId
 */
class CommitId
{
    /**
     * @var int
     */
    private $commitId;

    /**
     * @throws CommitException
     */
    public function __construct(int $commitId)
    {
        $this->assertIsIntegerOrMoreThanZero($commitId);

        $this->commitId = $commitId;
    }

    /**
     * @throws CommitException
     */
    private function assertIsIntegerOrMoreThanZero(int $commitId): void
    {
        if (!is_int($commitId) || 0 >= $commitId) {
            throw new CommitException(sprintf('Invalid Commit id: %s', var_export($commitId, true)));
        }
    }

    public function getValue(): int
    {
        return $this->commitId;
    }
}
