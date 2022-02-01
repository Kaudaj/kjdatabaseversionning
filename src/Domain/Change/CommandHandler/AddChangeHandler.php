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

namespace Kaudaj\Module\DBVCS\Domain\Change\CommandHandler;

use Kaudaj\Module\DBVCS\Domain\Change\Command\AddChangeCommand;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\CannotAddChangeException;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeException;
use Kaudaj\Module\DBVCS\Domain\Change\ValueObject\ChangeId;
use Kaudaj\Module\DBVCS\Entity\Change;
use PrestaShopException;

/**
 * Class AddChangeHandler is used for adding change data.
 */
final class AddChangeHandler extends AbstractChangeCommandHandler
{
    /**
     * @throws CannotAddChangeException
     * @throws ChangeException
     */
    public function handle(AddChangeCommand $command): ChangeId
    {
        try {
            $entity = new Change();

            if (null !== $command->getCommit()) {
                $entity->setCommit($command->getCommit());
            }

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (PrestaShopException $exception) {
            throw new ChangeException('An unexpected error occurred when adding change', 0, $exception);
        }

        return new ChangeId((int) $entity->getId());
    }
}
