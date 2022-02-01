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

use Kaudaj\Module\DBVCS\Domain\Change\Command\EditChangeCommand;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\CannotUpdateChangeException;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeException;
use PrestaShopException;

/**
 * Class EditChangeHandler is responsible for editing change data.
 *
 * @internal
 */
final class EditChangeHandler extends AbstractChangeCommandHandler
{
    /**
     * @throws ChangeException
     */
    public function handle(EditChangeCommand $command): void
    {
        try {
            $entity = $this->getChangeEntity(
                $command->getChangeId()->getValue()
            );

            if (null !== $command->getCommit()) {
                $entity->setCommit($command->getCommit());
            }

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (PrestaShopException $exception) {
            throw new CannotUpdateChangeException('An unexpected error occurred when editing change', 0, $exception);
        }
    }
}
