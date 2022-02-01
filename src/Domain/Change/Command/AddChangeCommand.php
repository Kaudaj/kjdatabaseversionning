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

/**
 * Class AddChangeCommand is responsible for adding change data.
 */
class AddChangeCommand
{
    /**
     * @var int|null
     */
    private $commit;

    /**
     * @return int|null
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @param int|null $commit
     */
    public function setCommit($commit): self
    {
        $this->commit = $commit;

        return $this;
    }
}
