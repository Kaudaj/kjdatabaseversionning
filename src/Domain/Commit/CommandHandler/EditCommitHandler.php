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

namespace Kaudaj\Module\DBVCS\Domain\Commit\CommandHandler;

use Kaudaj\Module\DBVCS\Domain\Commit\Command\EditCommitCommand;
use Kaudaj\Module\DBVCS\Domain\Commit\Exception\CannotUpdateCommitException;
use Kaudaj\Module\DBVCS\Domain\Commit\Exception\CommitException;
use Kaudaj\Module\DBVCS\Entity\CommitLang;
use PrestaShopBundle\Entity\Lang;
use PrestaShopException;

/**
 * Class EditCommitHandler is responsible for editing commit data.
 *
 * @internal
 */
final class EditCommitHandler extends AbstractCommitCommandHandler
{
    /**
     * @throws CommitException
     */
    public function handle(EditCommitCommand $command): void
    {
        try {
            $entity = $this->getCommitEntity(
                $command->getCommitId()->getValue()
            );

            foreach ($command->getLocalizedDescriptions() as $langId => $localizedDescription) {
                $commitLang = new CommitLang();

                $lang = $this->langRepository->find($langId);
                if (!($lang instanceof Lang)) {
                    continue;
                }

                $commitLang->setLang($lang);
                $commitLang->setDescription($localizedDescription->getValue());

                $entity->addCommitLang($commitLang);
            }

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (PrestaShopException $exception) {
            throw new CannotUpdateCommitException('An unexpected error occurred when editing commit', 0, $exception);
        }
    }
}
