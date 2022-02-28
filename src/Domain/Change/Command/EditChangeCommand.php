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

namespace Kaudaj\Module\DBVCS\Domain\Change\Command;

use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeException;
use Kaudaj\Module\DBVCS\Domain\Change\ValueObject\ChangeId;

/**
 * Class EditChangeCommand is responsible for editing change data.
 */
class EditChangeCommand
{
    /**
     * @var ChangeId
     */
    private $changeId;

    /**
     * @var int|null
     */
    private $commit;

    /**
     * @throws ChangeException
     */
    public function __construct(int $changeId)
    {
        $this->changeId = new ChangeId($changeId);
    }

    public function getChangeId(): ChangeId
    {
        return $this->changeId;
    }

    public function getCommit(): ?int
    {
        return $this->commit;
    }

    public function setCommit(?int $commit): self
    {
        $this->commit = $commit;

        return $this;
    }
}
