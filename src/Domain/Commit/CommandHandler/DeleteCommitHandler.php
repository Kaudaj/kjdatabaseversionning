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

use Doctrine\ORM\EntityManager;
use Exception;
use Kaudaj\Module\DBVCS\Domain\Commit\Command\DeleteCommitCommand;
use Kaudaj\Module\DBVCS\Domain\Commit\Exception\CannotDeleteCommitException;
use Kaudaj\Module\DBVCS\Domain\Commit\Exception\CommitException;
use Kaudaj\Module\DBVCS\Utils\VersionControlManager;
use PrestaShopBundle\Entity\Repository\LangRepository;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DeleteCommitHandler is responsible for deleting commit data.
 *
 * @internal
 */
final class DeleteCommitHandler extends AbstractCommitCommandHandler
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        EntityManager $entityManager,
        LangRepository $langRepository,
        Filesystem $filesystem
    ) {
        parent::__construct($entityManager, $langRepository);

        $this->filesystem = $filesystem;
    }

    /**
     * @throws CommitException
     */
    public function handle(DeleteCommitCommand $command): void
    {
        $commitId = $command->getCommitId()->getValue();

        $entity = $this->getCommitEntity($commitId);

        try {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            $commitPathname = VersionControlManager::COMMITS_DIR . "{$commitId}.php";

            if ($this->filesystem->exists($commitPathname)) {
                $this->filesystem->remove($commitPathname);
            }
        } catch (Exception $exception) {
            throw new CannotDeleteCommitException('An unexpected error occurred when deleting commit', 0, $exception);
        }
    }
}
