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

use Kaudaj\Module\DBVCS\Domain\Commit\Command\AddCommitCommand;
use Kaudaj\Module\DBVCS\Domain\Commit\Exception\CannotAddCommitException;
use Kaudaj\Module\DBVCS\Domain\Commit\Exception\CommitException;
use Kaudaj\Module\DBVCS\Domain\Commit\ValueObject\CommitId;
use Kaudaj\Module\DBVCS\Entity\Commit;
use Kaudaj\Module\DBVCS\Entity\CommitLang;
use PrestaShopBundle\Entity\Lang;
use PrestaShopException;

/**
 * Class AddCommitHandler is used for adding commit data.
 */
final class AddCommitHandler extends AbstractCommitCommandHandler
{
    /**
     * @throws CannotAddCommitException
     * @throws CommitException
     */
    public function handle(AddCommitCommand $command): CommitId
    {
        try {
            $entity = new Commit();

            foreach ($command->getLocalizedDescriptions() as $langId => $localizedDescription) {
                $commitLang = new CommitLang();

                /** @var Lang|null */
                $lang = $this->langRepository->find($langId);
                if (null === $lang) {
                    continue;
                }

                $commitLang->setLang($lang);
                $commitLang->setDescription($localizedDescription->getValue());

                $entity->addCommitLang($commitLang);
            }

            $entity->setDateAdd(new \DateTime('now', new \DateTimeZone('UTC')));

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (PrestaShopException $exception) {
            throw new CommitException('An unexpected error occurred when adding commit', 0, $exception);
        }

        return new CommitId((int) $entity->getId());
    }
}
