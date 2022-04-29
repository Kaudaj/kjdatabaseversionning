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

use Doctrine\ORM\EntityManager;
use Exception;
use Kaudaj\Module\DBVCS\Domain\Change\Command\DeleteChangeCommand;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\CannotDeleteChangeException;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeException;
use Kaudaj\Module\DBVCS\Utils\VersionControlManager;
use PrestaShopBundle\Entity\Repository\LangRepository;
use PrestaShopBundle\Entity\Repository\ShopGroupRepository;
use PrestaShopBundle\Entity\Repository\ShopRepository;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DeleteChangeHandler is responsible for deleting change data.
 *
 * @internal
 */
final class DeleteChangeHandler extends AbstractChangeCommandHandler
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        EntityManager $entityManager,
        LangRepository $langRepository,
        ShopRepository $shopRepository,
        ShopGroupRepository $shopGroupRepository,
        Filesystem $filesystem)
    {
        parent::__construct($entityManager, $langRepository, $shopRepository, $shopGroupRepository);

        $this->filesystem = $filesystem;
    }

    /**
     * @throws ChangeException
     */
    public function handle(DeleteChangeCommand $command): void
    {
        $changeId = $command->getChangeId()->getValue();

        $entity = $this->getChangeEntity($changeId);

        try {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            $changePathname = VersionControlManager::CHANGES_DIR . "{$changeId}.php";

            if ($this->filesystem->exists($changePathname)) {
                $this->filesystem->remove($changePathname);
            }
        } catch (Exception $exception) {
            throw new CannotDeleteChangeException('An unexpected error occurred when deleting change', 0, $exception);
        }
    }
}
